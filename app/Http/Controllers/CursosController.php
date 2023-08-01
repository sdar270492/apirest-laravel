<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Cursos;
use App\Clientes;

class CursosController extends Controller
{
    /*===================================================
    Mostrar todos los registros
    ===================================================*/
    public function index(Request $request) {

        $token = $request->header('Authorization');
        $clientes = Clientes::all();
        $json = array();


        foreach ($clientes as $key => $value) {
            if ("Basic ".base64_encode($value["id_cliente"].":".$value["llave_secreta"])==$token) {
                $cursos = Cursos::all();

                if (!empty($cursos)) {
                    $json = array(
                        "status" => 200,
                        "total_registros" => count($cursos),
                        "detalles" => $cursos
                    );
                } else {
                    $json = array(
                        "status" => 200,
                        "total_registros" => 0,
                        "detalles" => "No hay ningún curso registrado"
                    );
                }
            } else {
                $json = array(
                    "status" => 404,
                    "detalles" => "No esta autorizado para recibir los registros"
                );
            }
        }

        

        return json_encode($json, true);

    }

    /*===================================================
    Crear un registro
    ===================================================*/
    public function store(Request $request) {
        $token = $request->header('Authorization');
        $clientes = Clientes::all();
        $json = array();


        foreach ($clientes as $key => $value) {
            if ("Basic ".base64_encode($value["id_cliente"].":".$value["llave_secreta"])==$token) {

                // Recoger Datos
                $datos = array(
                                "titulo"=>$request->input("titulo"),
                                "descripcion"=>$request->input("descripcion"),
                                "instructor"=>$request->input("instructor"),
                                "imagen"=>$request->input("imagen"),
                                "precio"=>$request->input("precio"),
                              );


                if (!empty($datos)) {

                    // validar datos
                    $validator = Validator::make($datos, [
                        'titulo' => 'required|string|max:255|unique:cursos',
                        'descripcion' => 'required|string|max:255|unique:cursos',
                        'instructor' => 'required|string|max:255',
                        'imagen' => 'required|string|max:255|unique:cursos',
                        'precio' => 'required|numeric'
                    ]); 
                    
                    // Si falla la validación
                    if ($validator->fails()) {
                        $json = array(
                            "status" => 404,
                            "detalle" => "registro con errores: posible título repetido, posible descripción repetida, posible imagen repetida, no se permiten caracteres especiales"
                        );
                
                        return json_encode($json, true);
                    } else {
                        $cursos = new Cursos();
                        $cursos->titulo=$datos['titulo'];
                        $cursos->descripcion=$datos['descripcion'];
                        $cursos->instructor=$datos['instructor'];
                        $cursos->imagen=$datos['imagen'];
                        $cursos->precio=$datos['precio'];
                        $cursos->id_creador=$value['id'];

                        $cursos->save();
                        
                        $json = array(
                            "status" => 200,
                            "detalle" => "Registro exitoso, su curso ha sido guardado"
                        );

                        return json_encode($json, true);

                    }
                } else {
                    $json = array(
                        "status" => 404,
                        "detalle" => "Los registros no pueden estar vacíos"
                    );
                    
                }           

                // echo '<pre>'; print_r($datos); echo '</pre>';
                // return;

            } else {
                $json = array(
                    "status" => 404,
                    "detalles" => "No esta autorizado para recibir los registros"
                );
            }
        }

        return json_encode($json, true);
    }

}
