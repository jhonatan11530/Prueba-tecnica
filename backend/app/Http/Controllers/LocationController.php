<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LocationController extends Controller
{
    public function getPaises()
    {
        $paises = DB::table('paises')->get();
        return response()->json(['success' => true, 'data' => $paises]);
    }

    public function getCiudades($id)
    {
        $pais = DB::table('paises')->where('id', $id)->first();
        if (!$pais) {
            throw new NotFoundHttpException('País no encontrado');
        }

        $ciudades = DB::table('ciudades')->where('pais_id', $id)->get();
        return response()->json(['success' => true, 'data' => $ciudades]);
    }
}



