<?php

use Illuminate\Support\Facades\Schedule;
use App\Jobs\PublishScheduledPosts;

// Run every minute to check for posts scheduled at this specific time
Schedule::job(new PublishScheduledPosts)->everyMinute();
Schedule::command('posts:publish')->everyMinute();
Schedule::command('app:publish-scheduled-posts')->everyMinute();