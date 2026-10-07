<?php

namespace App\Http\Controllers;

use App\Services\CommandCenterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommandCenterController extends Controller
{
    protected CommandCenterService $service;

    public function __construct(CommandCenterService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('command-center.index');
    }

    public function process(Request $request): JsonResponse
    {
        try {
            $query = $request->input('query', '');
            
            if (empty(trim($query))) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'الاستعلام فارغ',
                ]);
            }
            
            $result = $this->service->process($query);
            
            return response()->json($result);
        } catch (\Throwable $e) {
            \Log::error('CommandCenter Error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            return response()->json([
                'type' => 'error',
                'message' => 'خطأ في المعالجة: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->input('q', '');
            if (strlen($query) < 2) {
                return response()->json(['entities' => []]);
            }
            $result = $this->service->searchEntities($query);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'خطأ في البحث: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function execute(Request $request): JsonResponse
    {
        try {
            $action = $request->input('action');
            $data = $request->input('data', []);
            $result = $this->service->executeForm($action, $data);
            return response()->json($result);
        } catch (\Throwable $e) {
            return response()->json([
                'type' => 'error',
                'message' => 'خطأ في التنفيذ: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function suggestions(): JsonResponse
    {
        try {
            return response()->json($this->service->getSuggestions());
        } catch (\Throwable $e) {
            return response()->json([], 500);
        }
    }
}