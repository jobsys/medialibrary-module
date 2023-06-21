<?php

namespace Modules\MediaLibrary\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Starter\Entities\Dictionary;

class MediaLibraryDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        if (!Dictionary::where('name', 'media_resource_category')->exists()) {
            $dict = Dictionary::create(['display_name' => '媒体库资源类型', 'name' => 'media_resource_category']);
            $dict->items()->insert([
                ['dictionary_id' => $dict->id, 'display_name' => '图片', 'value' => 'image'],
                ['dictionary_id' => $dict->id, 'display_name' => '视频', 'value' => 'video'],
                ['dictionary_id' => $dict->id, 'display_name' => '文件', 'value' => 'document'],
                ['dictionary_id' => $dict->id, 'display_name' => '音频', 'value' => 'audio'],
                ['dictionary_id' => $dict->id, 'display_name' => '其它', 'value' => 'other'],
            ]);
        }
    }
}
