<?php

namespace Modules\MediaLibrary\Http\Controllers;

use App\Http\Controllers\BaseManagerController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\MediaLibrary\Entities\LibraryMedia;
use Modules\Starter\Emnus\State;
use Pion\Laravel\ChunkUpload\Exceptions\UploadFailedException;
use Pion\Laravel\ChunkUpload\Handler\AbstractHandler;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class MediaLibraryController extends BaseManagerController
{


    /**
     * @throws UploadFailedException
     */
    public function upload(Request $request)
    {
        // check if the upload is success, throw exception or return response you need
        $receiver = new FileReceiver("file", $request, HandlerFactory::classFromRequest($request));

        // receive the file
        $save = $receiver->receive();

        // check if the upload has finished (in chunk mode it will send smaller files)
        if ($save->isFinished()) {
            $file = $save->getFile();
            $file_name = $this->createFilename($file);
            $name = $file->getClientOriginalName();
            $mime = $file->getMimeType();
            $date_folder = date("Ymd");

            $type = $request->input("type") === 'private' ? 'private' : 'public';
            $category = $request->input('category', $this->guessCategory($mime));
            $department_id = $request->input('department_id', 0);
            $is_private = $type === 'private';

            if ($is_private) {
                $file_path = "{$type}/{$mime}/{$date_folder}";
            } else {
                $file_path = "{$mime}/{$date_folder}";
            }
            // save the file and return any response you need
            if ($is_private) {
                $result = Storage::putFileAs($file_path, $file, $file_name);
            } else {
                $result = Storage::disk('public')->putFileAs($file_path, $file, $file_name);
            }

            if ($result) {
                $meta = [
                    "path" => $result,
                    "name" => $name,
                    "url" => $is_private
                        ? Storage::temporaryUrl($result, now()->addMinutes(120))
                        : Storage::disk('public')->url($result),
                ];

                $media = LibraryMedia::create([
                    'creator_id' => auth()->id(),
                    'department_id' => $department_id,
                    'name' => $name,
                    'category' => $category,
                    'extension' => $file->getClientOriginalExtension(),
                    'size' => $file->getSize(),
                    'media' => $meta,
                ]);

                return $this->json(array_merge($meta, [
                    "done" => 100,
                    "id" => $media->id,
                ]));
            } else {
                return $this->message("上传失败， 请重新上传");
            }
        }

        // we are in chunk mode, lets send the current progress
        /** @var AbstractHandler $handler */
        $handler = $save->handler();
        return response()->json([
            "done" => $handler->getPercentageDone()
        ]);
    }

    public function items(Request $request)
    {
        $department_id = $request->input('department_id', false);
        $category = $request->input('category', false);
        $extension = $request->input('extension', false);
        $name = $request->input('name', false);

        $pagination = LibraryMedia::authorise()->when($department_id, function ($query) use ($department_id) {
            return $query->where('department_id', $department_id);
        })->when($category, function ($query) use ($category) {
            return $query->where('category', $category);
        })->when($extension, function ($query) use ($extension) {
            return $query->where('extension', $extension);
        })->when($name, function ($query) use ($name) {
            return $query->where('name', 'like', "%{$name}%");
        })->orderByDesc('id')->paginate();

        return $this->json($pagination);

    }

    public function item(Request $request, $id)
    {
        $item = LibraryMedia::authorise()->where('id', $id)->first();
        return $this->json($item);
    }

    public function delete(Request $request)
    {
        $id = $request->input('id');
        $item = LibraryMedia::authorise()->where('id', $id)->first();

        if (!$item) {
            return $this->json(null, State::NOT_ALLOWED);
        }

        $result = $item->delete();

        return $this->json(null, $result ? State::SUCCESS : State::FAIL);
    }

    public function config(Request $request)
    {
        $categories = dict_get('media_resource_category');

        return $this->json(compact('categories'));
    }

    private function createFilename(UploadedFile $file)
    {
        $extension = $file->getClientOriginalExtension();
        $filename = str_replace("." . $extension, "", $file->getClientOriginalName()); // Filename without extension
        $filename = str_replace('-', '_', $filename); // Replace all dashes with underscores

        // Add timestamp hash to name of the file
        $filename .= "_" . md5(time()) . "." . $extension;

        return $filename;
    }

    private function guessCategory($mime)
    {
        $images = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml'];
        $videos = ['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/x-ms-wmv'];
        $audios = ['audio/mpeg', 'audio/x-wav', 'audio/x-ms-wma'];
        $documents = ['application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
        if (in_array($mime, $images)) {
            return 'image';
        } elseif (in_array($mime, $videos)) {
            return 'video';
        } elseif (in_array($mime, $audios)) {
            return 'audio';
        } elseif (in_array($mime, $documents)) {
            return 'document';
        } else {
            return 'other';
        }
    }
}
