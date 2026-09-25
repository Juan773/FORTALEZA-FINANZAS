<?php

use App\Livewire\Socios\Index as SociosIndex;
use App\Models\Legacy\SegUsuario;
use Livewire\Livewire;

it('filtra socios por tipo de usuario y vigencia igual que bo_gen_personas::listarSocioReporte', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    // Oráculo real (SQL directo): 161 socios tipo '02' (Socio) vigentes.
    Livewire::test(SociosIndex::class)
        ->set('filtroTipoUsuario', '02')
        ->set('filtroVigencia', '1')
        ->assertViewHas('socios', fn ($socios) => $socios->total() === 161);
});

it('exporta el listado de socios a Excel', function () {
    $this->actingAs((new SegUsuario())->forceFill(['cc_usuario' => '1', 'cc_user' => 'test']));

    Livewire::test(SociosIndex::class)
        ->call('exportarExcel')
        ->assertFileDownloaded('reporte-socios.xlsx');
});
