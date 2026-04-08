<?php

namespace TheJawker\Mediaux;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use TheJawker\Mediaux\Actions\CreateMediaItemFromRequestAction;
use TheJawker\Mediaux\Contracts\HasMediaContract;

class MediauxUploader
{
    private $associateMethod = null;

    private ?bool $private = null;

    public function __construct(public Request $request)
    {
    }

    public function private(bool $private = true): self
    {
        $this->private = $private;
        return $this;
    }

    public function public(): self
    {
        $this->private = false;
        return $this;
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
        $user = auth()->user();
        abort_unless($user instanceof HasMediaContract, 401);

        $mediaItem = (new CreateMediaItemFromRequestAction)->execute($user, $this->request, $this->private);

        if ($this->associateMethod) {
            ($this->associateMethod)($mediaItem);
        }

        return response()->json($mediaItem, 201);
    }
}
