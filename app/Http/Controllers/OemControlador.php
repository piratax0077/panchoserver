<?php

namespace App\Http\Controllers;

use App\oem;
use App\User;
use Illuminate\Http\Request;
use App\Imports\OemsImport;
use Maatwebsite\Excel\Facades\Excel;

class OemControlador extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\oem  $oem
     * @return \Illuminate\Http\Response
     */
    public function show(oem $oem)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\oem  $oem
     * @return \Illuminate\Http\Response
     */
    public function edit(oem $oem)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\oem  $oem
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, oem $oem)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\oem  $oem
     * @return \Illuminate\Http\Response
     */
    public function destroy(oem $oem)
    {
        //
    }

    public function prueba(){
      
        return view('manten.prueba');
    }
 
    public function import(Request $request)
        {
            
            $import = new OemsImport;
           
            try {
                Excel::import($import, request()->file('excel'));
                return view('inventario.factu_produ', ['numRows'=>$import->getRowCount()]);
            } catch (\Exception $e) {
                return $e;
            }
            
        }
}
