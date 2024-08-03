<?php
namespace App\misClases;

class ejemplo
{
	private $campo1;
	private $campo2;

	public function set1($d)
	{
		$this->campo1=$d;
	}

	public function set2($e)
	{
		$this->campo2=$e;
	}

	public function get1()
	{
		return $this->campo1;
	}

	public function get2()
	{
		return $this->campo2;
	}
}


?>
