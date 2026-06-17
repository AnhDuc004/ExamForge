<?php

namespace Database\Seeders;

use App\Modules\Answer\Models\Answer;
use App\Modules\Assignment\Models\Assignment;
use App\Modules\Attempt\Models\Attempt;
use App\Modules\Question\Models\Question;
use App\Modules\Role\Models\Role;
use App\Modules\Tenant\Models\Tenant;
use App\Modules\Test\Models\Test;
use App\Modules\Test\Models\TestSection;
use App\Modules\Test\Models\TestSectionQuestion;
use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoExamFlowSeeder extends Seeder
{
    private const PASSWORD = '12345678';

    public function run(): void
    {
        $tenant = Tenant::where('slug', 'default')->first();

        if (!$tenant) {
            $this->command->error('Default tenant not found. Run TenantSeeder first.');
            return;
        }

        $creator = $this->seedUser($tenant->id, 'creator@tenant.local', 'Question Creator', 'Question Creator');
        $reviewer = $this->seedUser($tenant->id, 'reviewer@tenant.local', 'Reviewer', 'Reviewer');
        $student = $this->seedUser($tenant->id, 'student@tenant.local', 'Student Ready', 'Student');
        $submittedStudent = $this->seedUser($tenant->id, 'submitted@tenant.local', 'Student Submitted', 'Student');
        $finalizedStudent = $this->seedUser($tenant->id, 'finalized@tenant.local', 'Student Finalized', 'Student');

        $questions = $this->seedQuestions($tenant->id, $creator->id);
        [$test, $mappings] = $this->seedPublishedTest($tenant->id, $creator->id, $questions);

        $this->seedAssignedFlow($tenant->id, $test->id, $student->id, $creator->id);
        $this->seedSubmittedFlow($tenant->id, $test->id, $submittedStudent->id, $creator->id, $mappings);
        $this->seedFinalizedFlow($tenant->id, $test->id, $finalizedStudent->id, $creator->id, $mappings);

        $this->command->info('Demo exam flow seeded.');
        $this->command->table(
            ['Account', 'Role', 'Password', 'Use case'],
            [
                ['admin@tenant.local', 'Tenant Admin', self::PASSWORD, 'manage users/roles/settings'],
                ['creator@tenant.local', 'Question Creator', self::PASSWORD, 'build and assign tests'],
                ['reviewer@tenant.local', 'Reviewer', self::PASSWORD, 'review, finalize, report'],
                ['student@tenant.local', 'Student', self::PASSWORD, 'start a fresh assignment'],
                ['submitted@tenant.local', 'Student', self::PASSWORD, 'has a submitted attempt pending grading'],
                ['finalized@tenant.local', 'Student', self::PASSWORD, 'has finalized result for reports'],
            ]
        );
    }

    private function seedUser(string $tenantId, string $email, string $displayName, string $roleName): User
    {
        $user = User::updateOrCreate(
            ['tenant_id' => $tenantId, 'email' => $email],
            [
                'display_name' => $displayName,
                'password_hash' => Hash::make(self::PASSWORD),
                'is_active' => true,
            ]
        );

        $role = Role::where('name', $roleName)->first();

        if ($role) {
            $user->roles()->syncWithoutDetaching([
                $role->id => [
                    'model_type' => User::class,
                ],
            ]);
        }

        return $user;
    }

    private function seedQuestions(string $tenantId, string $creatorId): array
    {
        return collect($this->hiraganaQuestionData())->map(function (array $payload) use ($tenantId, $creatorId) {
            return Question::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'content' => $payload['content'],
                ],
                [
                    'created_by' => $creatorId,
                    'type' => $payload['type'],
                    'options' => $payload['options'],
                    'correct_answer' => $payload['correct_answer'],
                    'max_score' => 1,
                    'difficulty' => $payload['difficulty'],
                    'tags' => ['demo', 'hiragana'],
                    'status' => 'published',
                ]
            );
        })->all();
    }

    private function seedPublishedTest(string $tenantId, string $creatorId, array $questions): array
    {
        $test = Test::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'title' => 'BÀI KIỂM TRA HIRAGANA (20 CÂU)',
            ],
            [
                'created_by' => $creatorId,
                'description' => 'Bộ đề demo kiểm tra Hiragana, âm đục, âm bán đục và romaji.',
                'duration_seconds' => 1800,
                'passing_score' => 16,
                'status' => 'published',
                'published_at' => now(),
            ]
        );

        $section = TestSection::updateOrCreate(
            [
                'test_id' => $test->id,
                'position' => 1,
            ],
            [
                'title' => 'Hiragana',
                'instructions' => 'Chọn đáp án đúng. Câu cuối nhập romaji.',
            ]
        );

        $mappings = [];
        foreach (array_values($questions) as $index => $question) {
            $mappings[] = TestSectionQuestion::updateOrCreate(
                [
                    'section_id' => $section->id,
                    'question_id' => $question->id,
                ],
                [
                    'position' => $index + 1,
                    'score_override' => null,
                    'question_snapshot' => $this->snapshotQuestion($question),
                ]
            );
        }

        return [$test, $mappings];
    }

    private function seedAssignedFlow(string $tenantId, string $testId, string $studentId, string $creatorId): void
    {
        Assignment::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'test_id' => $testId,
                'assignee_id' => $studentId,
            ],
            [
                'assigned_by' => $creatorId,
                'due_at' => now()->addDays(7),
                'max_attempts' => 2,
                'access_type' => 'account',
                'access_token' => null,
                'status' => 'assigned',
            ]
        );
    }

    private function seedSubmittedFlow(
        string $tenantId,
        string $testId,
        string $studentId,
        string $creatorId,
        array $mappings
    ): void {
        $assignment = Assignment::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'test_id' => $testId,
                'assignee_id' => $studentId,
            ],
            [
                'assigned_by' => $creatorId,
                'due_at' => now()->addDays(7),
                'max_attempts' => 1,
                'access_type' => 'account',
                'access_token' => null,
                'status' => 'submitted',
            ]
        );

        $attempt = Attempt::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'assignee_id' => $studentId,
            ],
            [
                'started_at' => now()->subMinutes(40),
                'submitted_at' => now()->subMinutes(10),
                'expires_at' => now()->addMinutes(20),
                'status' => 'submitted',
                'auto_score' => null,
                'manual_score' => null,
                'total_score' => null,
                'is_passed' => null,
                'is_finalized' => false,
            ]
        );

        foreach ($mappings as $mapping) {
            Answer::updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'test_section_question_id' => $mapping->id,
                ],
                [
                    'response' => $this->sampleResponse($mapping),
                    'auto_score' => null,
                    'manual_score' => null,
                    'review_status' => 'pending',
                ]
            );
        }
    }

    private function seedFinalizedFlow(
        string $tenantId,
        string $testId,
        string $studentId,
        string $creatorId,
        array $mappings
    ): void {
        $assignment = Assignment::updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'test_id' => $testId,
                'assignee_id' => $studentId,
            ],
            [
                'assigned_by' => $creatorId,
                'due_at' => now()->addDays(7),
                'max_attempts' => 1,
                'access_type' => 'account',
                'access_token' => null,
                'status' => 'finalized',
            ]
        );

        $attempt = Attempt::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'assignee_id' => $studentId,
            ],
            [
                'started_at' => now()->subDays(1)->subMinutes(40),
                'submitted_at' => now()->subDays(1)->subMinutes(10),
                'expires_at' => now()->subDays(1)->addMinutes(20),
                'status' => 'finalized',
                'auto_score' => count($mappings),
                'manual_score' => 0,
                'total_score' => count($mappings),
                'is_passed' => true,
                'is_finalized' => true,
            ]
        );

        foreach ($mappings as $mapping) {
            Answer::updateOrCreate(
                [
                    'attempt_id' => $attempt->id,
                    'test_section_question_id' => $mapping->id,
                ],
                [
                    'response' => $this->sampleResponse($mapping),
                    'auto_score' => 1,
                    'manual_score' => null,
                    'review_status' => 'auto_graded',
                ]
            );
        }
    }

    private function snapshotQuestion(Question $question): array
    {
        return [
            'id' => $question->id,
            'type' => $question->type,
            'content' => $question->content,
            'options' => $question->options,
            'correct_answer' => $question->correct_answer,
            'max_score' => $question->max_score,
            'difficulty' => $question->difficulty,
            'tags' => $question->tags,
            'status' => $question->status,
            'created_at' => $question->created_at,
            'updated_at' => $question->updated_at,
        ];
    }

    private function sampleResponse(TestSectionQuestion $mapping): array
    {
        $answer = $mapping->question_snapshot['correct_answer'][0] ?? null;

        return ['answer' => [$answer]];
    }

    private function hiraganaQuestionData(): array
    {
        return [
            $this->multipleChoice('Chữ nào đọc là ga?', [['a', 'か'], ['b', 'が'], ['c', 'げ'], ['d', 'ご']], 'b', 'easy'),
            $this->multipleChoice('Chữ nào đọc là zu?', [['a', 'ず'], ['b', 'ぞ'], ['c', 'ざ'], ['d', 'じ']], 'a', 'easy'),
            $this->multipleChoice('Chữ nào đọc là ba?', [['a', 'は'], ['b', 'ぱ'], ['c', 'ば'], ['d', 'び']], 'c', 'easy'),
            $this->multipleChoice('Chữ nào đọc là pi?', [['a', 'び'], ['b', 'ひ'], ['c', 'ぴ'], ['d', 'ぱ']], 'c', 'easy'),
            $this->multipleChoice('「ぎ」đọc là:', [['a', 'gi'], ['b', 'ge'], ['c', 'ga'], ['d', 'gu']], 'a', 'easy'),
            $this->multipleChoice('「ど」đọc là:', [['a', 'do'], ['b', 'de'], ['c', 'da'], ['d', 'du']], 'a', 'easy'),
            $this->multipleChoice('Chữ nào đọc là bo?', [['a', 'ぽ'], ['b', 'ぼ'], ['c', 'ば'], ['d', 'ぶ']], 'b', 'easy'),
            $this->multipleChoice('「ぱ」đọc là:', [['a', 'ba'], ['b', 'pa'], ['c', 'pi'], ['d', 'po']], 'b', 'easy'),
            $this->multipleChoice('Từ nào đọc là sushi?', [['a', 'すし'], ['b', 'そし'], ['c', 'せし'], ['d', 'さし']], 'a', 'medium'),
            $this->multipleChoice('「がくせい」đọc là:', [['a', 'gakusei'], ['b', 'gakusai'], ['c', 'gokusai'], ['d', 'gakushi']], 'a', 'medium'),
            $this->multipleChoice('「にほん」là:', [['a', 'nihon'], ['b', 'nihan'], ['c', 'nihen'], ['d', 'nihin']], 'a', 'medium'),
            $this->multipleChoice('「たなか」đọc là:', [['a', 'taneki'], ['b', 'tanaka'], ['c', 'tanako'], ['d', 'tanaki']], 'b', 'medium'),
            $this->multipleChoice('「ばなな」đọc là:', [['a', 'banana'], ['b', 'banena'], ['c', 'benana'], ['d', 'banani']], 'a', 'medium'),
            $this->multipleChoice('「ぱん」đọc là:', [['a', 'pan'], ['b', 'pen'], ['c', 'pon'], ['d', 'pin']], 'a', 'medium'),
            $this->multipleChoice('「みず」đọc là:', [['a', 'miji'], ['b', 'mizu'], ['c', 'misu'], ['d', 'mizo']], 'b', 'medium'),
            $this->multipleChoice('Chữ nào KHÔNG phải âm đục?', [['a', 'だ'], ['b', 'ざ'], ['c', 'が'], ['d', 'た']], 'd', 'medium'),
            $this->multipleChoice('Nhóm nào đều là âm bán đục?', [['a', 'ば び ぶ べ ぼ'], ['b', 'ぱ ぴ ぷ ぺ ぽ'], ['c', 'が ぎ ぐ げ ご'], ['d', 'ざ じ ず ぜ ぞ']], 'b', 'medium'),
            $this->multipleChoice('「じ」đọc là:', [['a', 'zi'], ['b', 'ji'], ['c', 'za'], ['d', 'zu']], 'b', 'medium'),
            $this->multipleChoice('「べんきょう」đọc là:', [['a', 'benkyou'], ['b', 'benkou'], ['c', 'benkiou'], ['d', 'benjyou']], 'a', 'hard'),
            [
                'type' => 'short_answer',
                'content' => '「がっこう」viết romaji là:',
                'options' => null,
                'correct_answer' => ['gakkou'],
                'difficulty' => 'hard',
            ],
        ];
    }

    private function multipleChoice(string $content, array $options, string $correctAnswer, string $difficulty): array
    {
        return [
            'type' => 'multiple_choice',
            'content' => $content,
            'options' => array_map(fn (array $option) => ['value' => $option[0], 'label' => $option[1]], $options),
            'correct_answer' => [$correctAnswer],
            'difficulty' => $difficulty,
        ];
    }
}
