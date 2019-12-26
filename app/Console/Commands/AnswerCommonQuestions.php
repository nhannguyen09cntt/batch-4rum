<?php

namespace App\Console\Commands;

use Discourse\Discourse;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class AnswerCommonQuestions extends Command
{
    const PAGE_LIMIT = 2;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'question:answer';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto-answer common questions at 4rum.vn';

    private $discourse;

    private $botAnswerClient;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->botAnswerClient = new Client();
        $this->discourse = new Discourse();
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        for ($page = 1; $page < self::PAGE_LIMIT; $page++) {
            $response = $this->discourse->get('/c/11.json', ['page' => $page]);
            $body = $response->getDecodedBody();
            if (empty($body['topic_list'])) {
                break;
            }
            $topics = $body['topic_list']['topics'];

            foreach ($topics as $topic) {
                $this->sendAnswer($topic);
            }
        }
    }

    private function sendAnswer($topic)
    {
        if ($topic['posts_count'] > 1 || $topic['last_poster_username'] != 'kimnguyet0815') {
            return;
        }
        $title = $topic['title'];
        $answer = '';
        $topic_data = ['title' => $topic['title'], 'url' => "https://4rum.vn/t/" . $topic['slug'] . "/" . $topic['id']];

        $answerRes = $this->botAnswerClient->request('POST', 'http://localhost:8080', [
            'body' => json_encode($topic_data),
        ]);
        $stringBody = (string) $answerRes->getBody();

        echo "Question: " . $title;
        echo "\n";
        echo "Answer: ";
        var_dump($stringBody);
    }
}
