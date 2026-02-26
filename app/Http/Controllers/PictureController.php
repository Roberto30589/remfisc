<?php

namespace App\Http\Controllers;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\Picture;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PictureController extends Controller
{
    public function store($file)
    {        
        try {

            //le asigna un nombre
            $filename = time().Str::random(10).".png";

            //construye la imagen con laravel intervention
            $manager = new ImageManager(Driver::class);
            $uploadedFile = $manager->read($file);

            //encodea la fotografia
            $imagedata = (string) $uploadedFile->toJpg();
            //$uploadedFile->orientate();

            //sube el archivo original
            $upload = Storage::disk('s3')->put(env('AWS_FOLDER').'/pictures/'.$filename, $imagedata,'public');

            if($upload){                
                $picture = new Picture;
                $picture->uri = env('AWS_URL').'pictures/'.$filename;
                $picture->anomaly_id = $anomaly_id; //se asigna la anomalía posteriormente
                $picture->name =  $filename;
                //solo si se guarda la fotografia la retorna de lo contrario retornara null
                if($picture->save()){
                    return $picture;
                }else{
                    return null;
                }
            }else{
                $status = false;
                //return view('pictures.process',compact('status','work_id'));
                return response()->json([
                    'message' => 'Error al cargar la Imagen',
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
