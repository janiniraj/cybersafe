<?php namespace App\Http\Transformers;
abstract class Transformer {
    public function transformCollection($items, $function = 'transform')
    {
        return array_map([$this, $function], $items);
    }
    public abstract function transform($item);

    public function nulltoBlank($data)
    {
        return $data ? $data : '';
    }
}