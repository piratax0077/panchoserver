<?php
namespace App\misClases;

//Esta clase la uso para devolver los datos del repuesto y su último ingreso por compra.
//Se procesa en factuprodu_controlador@buscarepuesto y se muestra en factu_produ.blade function javascript buscarRepuesto().
class factu_produ
{

	private $codigo_interno;
	private $id_familia;

	public function setCodigoInterno($a)
	{
		$this->codigo_interno=$a;
	}

	public function setFamilia($b)
	{
		$this->id_familia=$b;
	}
}
