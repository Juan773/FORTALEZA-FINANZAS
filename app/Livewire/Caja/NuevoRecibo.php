<?php

namespace App\Livewire\Caja;

use App\Models\Legacy\FinComprobante;
use App\Models\Legacy\FinConcepto;
use App\Models\Legacy\GenBanco;
use App\Models\Legacy\GenCuenta;
use App\Models\Legacy\GenPersona;
use App\Services\CajaService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Reemplaza caja/caj_index.php + caj_det_form_temp.php + caj_update.php.
 * A diferencia del legacy (que arma el detalle en una tabla temporal en BD
 * ligada a la sesión), el carrito vive en el propio estado del componente
 * Livewire mientras se arma el recibo — mismo resultado final, sin la
 * tabla fin_caja_detalle_temp ni el session-id (s_cod_unico) que la
 * acompañaba en el original.
 */
class NuevoRecibo extends Component
{
    public string $buscarSocio = '';
    public ?string $cc_persona = null;
    public string $nombreSocio = '';

    public string $ct_serie = '';
    public string $pag_tipo = '';
    public string $pag_fecha = '';
    public string $pag_nop = '';
    public string $cc_banco = '';
    public string $cc_cuenta = '';
    public string $caj_obs = '';

    public string $nuevoConcepto = '';
    public string $nuevaCantidad = '1';
    public string $nuevoImporte = '0';

    public array $carrito = [];

    public function resultadosBusqueda()
    {
        if (strlen($this->buscarSocio) < 2) {
            return collect();
        }

        return GenPersona::where('cfl_vigencia', '1')
            ->where(fn ($q) => $q->where('ct_nombres', 'like', '%'.$this->buscarSocio.'%')
                ->orWhere('ct_nro_doc', 'like', '%'.$this->buscarSocio.'%'))
            ->limit(8)->get();
    }

    public function elegirSocio(string $ccPersona, string $nombre): void
    {
        $this->cc_persona = $ccPersona;
        $this->nombreSocio = $nombre;
        $this->buscarSocio = '';
    }

    public function agregarLinea(): void
    {
        $this->validate([
            'nuevoConcepto' => ['required'],
            'nuevaCantidad' => ['required', 'numeric', 'min:0.01'],
            'nuevoImporte' => ['required', 'numeric', 'min:0.01'],
        ]);

        $concepto = FinConcepto::find($this->nuevoConcepto);

        $this->carrito[] = [
            'cc_concepto' => $this->nuevoConcepto,
            'nombre' => $concepto->ct_nombre,
            'ct_cantidad' => $this->nuevaCantidad,
            'ct_importe' => $this->nuevoImporte,
            'ct_total' => round((float) $this->nuevaCantidad * (float) $this->nuevoImporte, 2),
        ];

        $this->reset(['nuevoConcepto', 'nuevaCantidad', 'nuevoImporte']);
        $this->nuevaCantidad = '1';
        $this->nuevoImporte = '0';
    }

    public function quitarLinea(int $indice): void
    {
        unset($this->carrito[$indice]);
        $this->carrito = array_values($this->carrito);
    }

    public function guardar(CajaService $cajaService): void
    {
        $this->validate([
            'cc_persona' => ['required'],
            'ct_serie' => ['required'],
            'pag_tipo' => ['required', 'in:E,V'],
            'cc_banco' => [$this->pag_tipo === 'V' ? 'required' : 'nullable'],
            'cc_cuenta' => [$this->pag_tipo === 'V' ? 'required' : 'nullable'],
        ]);

        if (empty($this->carrito)) {
            $this->addError('carrito', 'Agrega al menos un concepto al recibo.');

            return;
        }

        $cajaService->crearRecibo([
            'cc_persona' => $this->cc_persona,
            'ct_serie' => $this->ct_serie,
            'caj_fecha' => now(),
            'pag_tipo' => $this->pag_tipo,
            'pag_fecha' => $this->pag_fecha ?: null,
            'pag_nop' => $this->pag_nop ?: null,
            'cc_banco' => $this->pag_tipo === 'V' ? $this->cc_banco : null,
            'cc_cuenta' => $this->pag_tipo === 'V' ? $this->cc_cuenta : null,
            'caj_obs' => $this->caj_obs ?: null,
            'cc_usuario' => Auth::id(),
        ], $this->carrito);

        session()->flash('status', 'Recibo emitido correctamente.');
        $this->reset(['cc_persona', 'nombreSocio', 'pag_tipo', 'pag_fecha', 'pag_nop', 'cc_banco', 'cc_cuenta', 'caj_obs', 'carrito']);
    }

    public function cuentasDelBanco()
    {
        return $this->cc_banco ? GenCuenta::where('cc_banco', $this->cc_banco)->get() : collect();
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.caja.nuevo-recibo', [
            'resultados' => $this->resultadosBusqueda(),
            'comprobantes' => FinComprobante::where('ct_vigencia', '1')->get(),
            'conceptos' => FinConcepto::where('ct_tipo', 'I')->where('ct_vigencia', '1')->orderBy('ct_nombre')->get(),
            'bancos' => GenBanco::where('ct_vigencia', '1')->get(),
            'cuentas' => $this->cuentasDelBanco(),
        ]);
    }
}
