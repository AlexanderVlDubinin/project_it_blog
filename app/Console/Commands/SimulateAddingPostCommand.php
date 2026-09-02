<?php

namespace App\Console\Commands;

use App\Services\AddingPost;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Command\Command as CommandAlias;

#[Signature('simulate:adding-post
    {newsNum=1 : The number of news (articles) which will be downloaded}
    {userId? : The ID of the user that the user specified as the author (if not specified, a random author will be selected)}
    {--addTags : When using this option, when saving a news item to the database, tags from existing ones will be added to it}
    {--dryRun : When using this option, news data will be collected but will not be saved to the database} '
)]
#[Description('The command simulates the addition of a post by one of the authors. News articles from the Internet are used as new posts.')]
class SimulateAddingPostCommand extends Command
{
    public function __construct(
        private readonly AddingPost $addingPost,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * Command description:
     * - Simulates the addition of a post by one of the authors.
     * - News articles from the Internet are used as new posts.
     * - Actions are logged in the 'custom-commands' channel.
     */
    public function handle(): int
    {
        // Get command arguments
        $newsNum = $this->argument('newsNum') ?? 1;
        $userId = $this->argument('userId') ?? 0;
        $dryRun = $this->option('dryRun') ?? false;
        $addTags = $this->option('addTags') ?? false;

        // Validate user ID
        $validatedData = $this->addingPost->beforeImportNews($userId);
        if ($validatedData['status'] === 'error') {
            Log::channel('custom-commands')->error($validatedData['message']);
            $this->error($validatedData['message']);

            return CommandAlias::FAILURE;
        }
        $userId = $validatedData['user_id'];

        // Log start message
        $startMessage = 'Start importing news with parameters: newsNum = '.$newsNum;
        $startMessage .= $userId ? ', userId = '.$userId : '';
        $startMessage .= $addTags ? ', addTags = true' : '';
        $startMessage .= $dryRun ? ', dryRun = true' : '';

        $logChannel = 'custom-commands';
        Log::channel($logChannel)->info($startMessage);

        // Import news - main logic
        $result = $this->addingPost->importNews($userId, $logChannel, $newsNum, $addTags, $dryRun);

        if ($result['status'] === 'error') {
            $this->error($result['message']);
            Log::channel($logChannel)->error($result['message']);

            return CommandAlias::FAILURE;
        }

        // Log end message
        Log::channel($logChannel)->info($result['message']);
        $this->info($result['message']);

        return CommandAlias::SUCCESS;
    }
}
