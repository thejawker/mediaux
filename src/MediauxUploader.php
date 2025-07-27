<?php

namespace TheJawker\Mediaux;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TheJawker\Mediaux\Actions\CreateMediaItemFromRequestAction;

class MediauxUploader
{
    private $associateMethod = null;

    public function __construct(public Request $request)
    {
    }

    public function customValidation(array $rules, ...$params)
    {
        $this->validate(array_merge([
            'file' => ['required', 'file'],
        ], $rules), ...$params);
        return $this;
    }

    private function validate(array $rules, ...$params)
    {
        $this->request->validate([
            'file' => ['required', 'file'],
        ]);
    }

    public function associate(callable $associateCallback): self
    {
        $this->associateMethod = $associateCallback;
        return $this;
    }

    public function respond(): JsonResponse
    {
        $mediaItem = (new CreateMediaItemFromRequestAction)->execute(auth()->user(), $this->request);

        if ($this->associateMethod) {
            ($this->associateMethod)($mediaItem);
        }

        return response()->json($mediaItem, 201);
    }
}
