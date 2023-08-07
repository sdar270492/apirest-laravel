<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Clientes;

class ClientesController extends Controller
{
    public function index() {

        $json = array(
            "detalle" => "no encontrado"
        );

        echo json_encode($json, true);
    }

    /*===================================================
    Crear un registro
    ===================================================*/
    public function store(Request $request) {
        $datos = array("primer_nombre" => $request->input("primer_nombre"),
                       "primer_apellido" => $request->input("primer_apellido"),
                       "email" => $request->input("email"));
        
        if (!empty($datos)) {
            // Validar datos
            $validator = Validator::make($datos, [
                'primer_nombre' => 'required|string|max:255',
                'primer_apellido' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:clientes',
            ]);

            // Si falla la validación
            if ($validator->fails()) {

                $errors = $validator->errors();

                $json = array(
                    "status" => 404,
                    "detalle" => $errors
                );
        
                return json_encode($json, true);
            } else {
                $id_cliente = Hash::make($datos["primer_nombre"].$datos["primer_apellido"].$datos["email"]);
                $llave_secreta = Hash::make($datos["email"].$datos["primer_apellido"].$datos["primer_nombre"], ['rounds' => 12]);

                $cliente = new Clientes();
                $cliente->primer_nombre = $datos['primer_nombre'];
                $cliente->primer_apellido = $datos['primer_apellido'];
                $cliente->email = $datos['email'];
                $cliente->id_cliente = str_replace('$', 'a', $id_cliente);
                $cliente->llave_secreta = str_replace('$', 'b', $llave_secreta);

                $cliente->save();

                $json = array(
                    "status" => 200,
                    "detalle" => "Registro exitoso, tome sus credenciales y guardelas",
                    "credenciales" => array("id_cliente" => str_replace('$', 'a', $id_cliente),"llave_secreta" => str_replace('$', 'b', $llave_secreta))
                );

                return json_encode($json, true);

            }
        } else {
            $json = array(
                "status" => 404,
                "detalle" => "Los registros no pueden estar vacíos"
            );

            return json_encode($json, true);
        }
    }
}
