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

    /*===================================================
    Tomar un registro
    ===================================================*/
    public function show($id, Request $request){
        $token = $request->header('Authorization');
        $clientes = Clientes::all();
        $json = array();

        foreach ($clientes as $key => $value) {
            if ("Basic ".base64_encode($value["id_cliente"].":".$value["llave_secreta"])==$token) {
                $curso = Cursos::where("id", $id)->get();

                if (!empty($curso)) {
                    $json = array(
                        "status" => 200,
                        "detalles" => $curso
                    );
                } else {
                    $json = array(
                        "status" => 200,
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
    Editar un registro
    ===================================================*/
    public function update($id, Request $request) {
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
                        'titulo' => 'required|string|max:255',
                        'descripcion' => 'required|string|max:255',
                        'instructor' => 'required|string|max:255',
                        'imagen' => 'required|string|max:255',
                        'precio' => 'required|numeric'
                    ]); 
                    
                    // Si falla la validación
                    if ($validator->fails()) {
                        $json = array(
                            "status" => 404,
                            "detalle" => "registro con errores: No se permiten caracteres especiales"
                        );
                
                        return json_encode($json, true);
                    } else {
                        $traer_curso = Cursos::where("id", $id)->get();

                        if ($value["id"] == $traer_curso[0]["id_creador"]){
                        
                            $datos = array("titulo" => $datos["titulo"],
                                           "descripcion" => $datos["descripcion"],
                                           "instructor" => $datos["instructor"],
                                           "imagen" => $datos["imagen"],
                                           "precio" => $datos["precio"]);

                            $cursos = Cursos::where("id", $id)->update($datos);
                            
                            $json = array(
                                "status" => 200,
                                "detalle" => "Registro exitoso, su curso ha sido actualizado"
                            );

                            return json_encode($json, true);
                        } else {
                            $json = array(
                                "status" => 404,
                                "detalle" => "No está autorizado para modificar este curso"
                            );

                            return json_encode($json, true);
                        }
                        
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

    /*===================================================
    Eliminar un registro
    ===================================================*/
    public function destroy($id, Request $request) {
        $token = $request->header('Authorization');
        $clientes = Clientes::all();
        $json = array();


        foreach ($clientes as $key => $value) {
            if ("Basic ".base64_encode($value["id_cliente"].":".$value["llave_secreta"])==$token) {

                $validar = Cursos::where("id", $id)->get();

                if (!empty($validar)) {
                    if ($value["id"] == $validar[0]["id_creador"]){
                        
                        $curso = Cursos::where("id", $id)->delete();
                        
                        $json = array(
                            "status" => 200,
                            "detalle" => "Se ha borrado su curso"
                        );

                        return json_encode($json, true);
                    } else {
                        $json = array(
                            "status" => 404,
                            "detalle" => "No está autorizado para eliminar este curso"
                        );

                        return json_encode($json, true);
                    }
                } else {
                    $jason = array(
                        "status" => 404,
                        "detalle" => "El curso no existe"
                    );

                    return json_encode($jason, true);
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
}
