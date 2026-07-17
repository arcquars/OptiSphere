<?php
namespace App\Http\Controllers;

use App\Models\ProductAuthentication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

class ProductAuthenticationController extends Controller
{
    public function show($token)
    {
        try {
            // 1. Revertimos el reemplazo de caracteres URL-safe a su estado original
            $originalEncrypted = str_replace(['-', '_'], ['+', '/'], $token);
            
            // 2. Desencriptamos el ID real de la tabla
            $id = Crypt::decrypt($originalEncrypted);
            
        } catch (DecryptException $e) {
            // Si el token expiró, fue alterado o es inválido, lanzamos un 404
            abort(404, "Enlace de autenticación inválido.");
        }

        // 3. Buscamos el registro por su ID (si no existe, lanza un 404 automáticamente)
        $auth = ProductAuthentication::findOrFail($id);

        // 4. Regla de negocio: Solo accesible si 'is_authentication' es igual a 1
        if ($auth->is_authentication != 1) {
            // Puedes retornar un 403 (No autorizado) o una vista personalizada
            abort(403, "Este producto no cuenta con una autenticación activa.");
        }

        // 5. Retornar la vista con los datos
        return view('product_authentication.show', compact('auth'));
    }
}