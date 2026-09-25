<?php

namespace App\Livewire\Usuarios;

use App\Models\Auditoria;
use App\Models\Legacy\GenPersona;
use App\Models\Legacy\SegPerfil;
use App\Models\Legacy\SegUsuario;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Reemplaza seguridad/usu_index.php + usu_lista.php + usu_form.php + usu_update.php.
 *
 * Regla replicada TAL CUAL, por decisión explícita del cliente (no es un descuido
 * nuestro): al crear un usuario nuevo, la clave inicial NO es la que se escribe en
 * el formulario — es el DNI del socio (ver bo_seg_usuario + dao_seg_usuario::insertar,
 * hallazgo documentado en la sesión de migración). Al editar, en cambio, si se
 * escribe una clave nueva sí se usa esa (igual que dao_seg_usuario::modificar);
 * si se deja en blanco, la clave actual no se toca.
 */
class Index extends Component
{
    public string $buscarSocio = '';
    public ?string $cc_persona_nuevo = null;
    public string $nombreSocioNuevo = '';

    public bool $mostrandoFormulario = false;
    public ?string $cc_usuario = null;
    public string $cc_user = '';
    public string $cc_perfil = '';
    public bool $cfl_acceso = true;
    public string $ct_clave = '';

    protected array $reglasComunes = [
        'cc_user' => ['required', 'string', 'min:5', 'max:30', 'regex:/^[a-zA-Z0-9_.]+$/'],
        'cc_perfil' => ['required'],
    ];

    public function resultadosBusquedaSocios()
    {
        if (strlen($this->buscarSocio) < 2) {
            return collect();
        }

        return GenPersona::whereNotIn('cc_persona', DB::connection('legacy')->table('seg_usuario')->pluck('cc_usuario'))
            ->where('ct_nombres', 'like', '%'.$this->buscarSocio.'%')
            ->limit(8)->get();
    }

    public function elegirSocioNuevo(string $ccPersona, string $nombre): void
    {
        $this->cc_persona_nuevo = $ccPersona;
        $this->nombreSocioNuevo = $nombre;
        $this->buscarSocio = '';
        $this->cc_usuario = null;
        $this->cc_user = '';
        $this->cc_perfil = '';
        $this->cfl_acceso = true;
        $this->ct_clave = '';
        $this->mostrandoFormulario = true;
    }

    public function editar(string $ccUsuario): void
    {
        $usuario = SegUsuario::findOrFail($ccUsuario);

        $this->cc_usuario = $usuario->cc_usuario;
        $this->cc_persona_nuevo = null;
        $this->nombreSocioNuevo = $usuario->ct_nombres;
        $this->cc_user = $usuario->cc_user;
        $this->cc_perfil = (string) $usuario->cc_perfil;
        $this->cfl_acceso = $usuario->cfl_acceso == 1;
        $this->ct_clave = '';
        $this->mostrandoFormulario = true;
    }

    public function guardar(): void
    {
        $this->validate($this->reglasComunes);

        $usuarioDuplicado = DB::connection('legacy')->table('seg_usuario')
            ->where('cc_user', $this->cc_user)
            ->when($this->cc_usuario, fn ($q) => $q->where('cc_usuario', '<>', $this->cc_usuario))
            ->exists();

        if ($usuarioDuplicado) {
            $this->addError('cc_user', 'Ese nombre de usuario ya existe.');

            return;
        }

        if ($this->cc_usuario) {
            $datos = [
                'cc_user' => $this->cc_user,
                'cc_perfil' => $this->cc_perfil,
                'cfl_acceso' => $this->cfl_acceso ? '1' : '0',
                'cc_usuario_audit' => Auth::id(),
                'ct_ip' => request()->ip(),
                'df_log' => now(),
            ];

            if ($this->ct_clave !== '') {
                $datos['ct_clave'] = sha1($this->ct_clave);
                $datos['df_caduca'] = now()->addYear();
            }

            DB::connection('legacy')->table('seg_usuario')->where('cc_usuario', $this->cc_usuario)->update($datos);

            Auditoria::registrar('usuario.editar', "Editó el usuario {$this->cc_user}", [
                'cc_usuario' => $this->cc_usuario,
                'cc_perfil' => $this->cc_perfil,
                'cfl_acceso' => $this->cfl_acceso,
                'clave_cambiada' => $this->ct_clave !== '',
            ]);
        } else {
            $persona = GenPersona::findOrFail($this->cc_persona_nuevo);

            DB::connection('legacy')->table('seg_usuario')->insert([
                'cc_usuario' => $persona->cc_persona,
                'cc_user' => $this->cc_user,
                'cc_perfil' => $this->cc_perfil,
                'nn_tiempo_sesion' => 3600,
                'cfl_acceso' => $this->cfl_acceso ? '1' : '0',
                'cfl_clave_cambia' => '0',
                'ct_clave' => sha1($persona->ct_nro_doc),
                'df_caduca' => now()->addYear(),
                'cc_usuario_audit' => Auth::id(),
                'df_log' => now(),
                'ct_ip' => request()->ip(),
            ]);

            Auditoria::registrar('usuario.crear', "Creó el usuario {$this->cc_user} para {$persona->ct_nombres}", [
                'cc_usuario' => $persona->cc_persona,
                'cc_perfil' => $this->cc_perfil,
            ]);
        }

        $this->mostrandoFormulario = false;
        session()->flash('status', 'Usuario guardado correctamente.');
    }

    public function cancelar(): void
    {
        $this->mostrandoFormulario = false;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.usuarios.index', [
            'usuarios' => SegUsuario::orderBy('ct_nombres')->get(),
            'perfiles' => SegPerfil::where('cfl_vigencia', '1')->orderBy('ct_perfil')->get(),
            'resultadosSocios' => $this->resultadosBusquedaSocios(),
        ]);
    }
}
