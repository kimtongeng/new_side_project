<?php

namespace Modules\Loan\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Google\Cloud\AIPlatform\V1\PredictionServiceClient;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Validator;

class VertexAIController extends Controller
{
    protected $projectId = 'your-project-id';
    protected $location = 'us-central1';
    protected $modelId = 'veo-3.0-generate-preview';
    protected $bucketName = 'your-video-bucket';
    protected $serviceAccountKeyPath = '/home/alsemsar/credentials/service-account-key.json';

    /**
     * Display the Vertex AI page.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('Loan::vertex-ai.index', [
            'videos' => [],
            'error' => null,
        ]);
    }

    /**
     * Generate a video using Vertex AI.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function generate(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'prompt' => 'required|string|max:500',
            'aspectRatio' => 'required|in:16:9',
            'duration' => 'required|integer|in:5,6,7,8',
            'sampleCount' => 'required|integer|in:1,2,3,4',
        ]);

        if ($validator->fails()) {
            return redirect()->route('Loan.vertex-ai.index')
                ->withErrors($validator)
                ->withInput();
        }

        $prompt = $request->input('prompt');
        $aspectRatio = $request->input('aspectRatio', '16:9');
        $duration = (int) $request->input('duration', 5);
        $sampleCount = (int) $request->input('sampleCount', 1);

        try {
            // Initialize Google Cloud client
            $credentials = new ServiceAccountCredentials(
                ['https://www.googleapis.com/auth/cloud-platform'],
                $this->serviceAccountKeyPath
            );
            $client = new PredictionServiceClient([
                'credentials' => $credentials,
            ]);

            // Prepare request
            $endpoint = sprintf(
                'projects/%s/locations/%s/publishers/google/models/%s',
                $this->projectId,
                $this->location,
                $this->modelId
            );
            $instance = ['prompt' => $prompt];
            $parameters = [
                'aspectRatio' => $aspectRatio,
                'durationSeconds' => $duration,
                'sampleCount' => $sampleCount,
                'storageUri' => "gs://{$this->bucketName}/output/",
                'personGeneration' => 'allow_adult',
            ];

            // Send prediction request
            $response = $client->predictLongRunning([
                'endpoint' => $endpoint,
                'instances' => [$instance],
                'parameters' => $parameters,
            ]);

            // Wait for operation to complete
            $operation = $response->getOperation();
            $result = $operation->pollUntilComplete();
            $videos = [];
            if ($result->hasGeneratedSamples()) {
                foreach ($result->getGeneratedSamples() as $sample) {
                    $videos[] = $sample->getVideo()->getUri();
                }
            }

            return redirect()->route('Loan.vertex-ai.index')
                ->with('videos', $videos)
                ->with('success', __('Loan::lang.video_generated_successfully'));
        } catch (\Exception $e) {
            return redirect()->route('Loan.vertex-ai.index')
                ->with('error', __('Loan::lang.video_generation_failed') . ': ' . $e->getMessage())
                ->withInput();
        }
    }
}