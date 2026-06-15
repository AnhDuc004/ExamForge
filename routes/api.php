<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['api'])->group(function () {
    require __DIR__ . '/modules/auth.php';

    require __DIR__ . '/modules/tenant.php';

    require __DIR__ . '/modules/user.php';

    require __DIR__ . '/modules/role.php';

    require __DIR__ . '/modules/permission.php';

    require __DIR__ . '/modules/question.php';

    require __DIR__ . '/modules/assignment.php';

    require __DIR__ . '/modules/test.php';

    // Additional module routes can be included here:
    // require __DIR__ . '/modules/attempt.php';
    // require __DIR__ . '/modules/answer.php';
});
