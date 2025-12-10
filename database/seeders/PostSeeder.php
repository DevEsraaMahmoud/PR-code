<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Snippet;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a user
        $user = User::firstOrCreate(
            ['email' => 'developer@example.com'],
            [
                'name' => 'John Developer',
                'password' => bcrypt('password'),
            ]
        );

        // Sample PHP code for payment webhook handler
        $codeSnippet = <<<'PHP'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        $payload = $request->all();
        
        DB::transaction(function () use ($payload) {
            $payment = Payment::create([
                'transaction_id' => $payload['id'],
                'amount' => $payload['amount'],
                'status' => $payload['status'],
            ]);
            
            if ($payment->status === 'completed') {
                $this->processPayment($payment);
            }
        });
        
        return response()->json(['status' => 'ok']);
    }
    
    private function processPayment($payment)
    {
        // Process payment logic here
        Log::info('Payment processed', ['payment_id' => $payment->id]);
    }
}
PHP;

        // Create the post
        $post = Post::create([
            'user_id' => $user->id,
            'title' => 'PR: add payment webhook handler',
            'slug' => 'pr-add-payment-webhook-handler',
            'body' => [
                [
                    'type' => 'text',
                    'content' => 'This PR adds a webhook handler for processing payment notifications from our payment provider.',
                ],
                [
                    'type' => 'code',
                    'language' => 'php',
                    'content' => $codeSnippet,
                ],
                [
                    'type' => 'text',
                    'content' => 'The handler uses database transactions to ensure data consistency and includes proper error handling.',
                ],
            ],
            'visibility' => 'public',
        ]);

        // Create the code snippet
        $snippet = Snippet::create([
            'post_id' => $post->id,
            'language' => 'php',
            'code_text' => $codeSnippet,
            'block_index' => 1,
        ]);

        // Create inline comments
        $comment1 = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'snippet_id' => $snippet->id,
            'is_inline' => true,
            'start_line' => 4,
            'end_line' => 4,
            'body' => 'Should we add validation for the payload structure?',
        ]);

        $comment2 = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'snippet_id' => $snippet->id,
            'is_inline' => true,
            'start_line' => 9,
            'end_line' => 9,
            'body' => 'Consider using a queue job for async processing instead of processing synchronously.',
        ]);

        $comment3 = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'snippet_id' => $snippet->id,
            'is_inline' => true,
            'start_line' => 15,
            'end_line' => 15,
            'body' => 'Good use of DB::transaction here! This ensures atomicity.',
        ]);

        // Create general comments
        $generalComment1 = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'snippet_id' => null,
            'is_inline' => false,
            'start_line' => null,
            'end_line' => null,
            'body' => 'Overall, this looks good! One suggestion: consider adding rate limiting to prevent abuse.',
        ]);

        $generalComment2 = Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'snippet_id' => null,
            'is_inline' => false,
            'start_line' => null,
            'end_line' => null,
            'body' => 'Should we add tests for this webhook handler?',
        ]);

        // Create a reply to the first general comment
        Comment::create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'snippet_id' => null,
            'parent_id' => $generalComment1->id,
            'is_inline' => false,
            'start_line' => null,
            'end_line' => null,
            'body' => 'Good point! I\'ll add rate limiting in the next commit.',
        ]);

        $this->command->info('Created sample post with code snippet and comments!');
        $this->command->info("Post ID: {$post->id}");
        $this->command->info("Post URL: /posts/{$post->slug}");
    }
}


