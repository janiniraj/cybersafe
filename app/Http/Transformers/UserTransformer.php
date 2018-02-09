<?php
namespace App\Http\Transformers;
use App\Http\Transformers\Transformer;
use URL;
class UserTransformer extends Transformer
{
    public function transform($data)
    {
        return [
            'id'        => $data['id'],
            'username'  => $this->nulltoBlank($data['username']),
            'name'      => $this->nulltoBlank($data['name']),
            'email'     => $this->nulltoBlank($data['email']),
            'location'  => $this->nulltoBlank($data['location']),
            'image'     => $data['image'] ? URL::to('/').'/uploads/users/'.$data['image'] : URL::to('/').'/uploads/users/default.png',
            'is_follow' => (isset($data['is_follow']) && $data['is_follow']) ? 1 : 0
        ];
    }

    public function transformToken($data)
    {
        return[
            'token' => $data['token'],
            'type'  => $data['type']
        ];
    }
}