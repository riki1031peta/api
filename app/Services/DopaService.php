<?php

namespace App\Services;

use App\Models\Dopa;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DopaService
{
    public const MAX_LEVEL = 100;

    public const EXP = [
        'blog_created' => 200,
        'comment_created' => 100,
        'favorite_created' => 50,
    ];

    public const STAGE_NAMES = [
        'egg' => 'ドパタマゴ',
        '1' => 'ドパ子丼',
        '2' => 'ドパ',
        '3' => 'ドパドパ',
        '4' => 'スーパードパ',
        '5' => 'ウルトラドパ',
        '6' => '？？？',
    ];

    /**
     * 進化段階の表示名を取得
     */
    public function stageName(string $stage): string
    {
        return self::STAGE_NAMES[$stage] ?? '不明';
    }

    /**
     * 指定レベルに到達するための累計EXP
     */
    public function requiredExperience(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }

        return (int) (100 * pow($level - 1, 2) + 50 * ($level - 1));
    }

    /**
     * EXPから現在レベルを計算
     */
    public function calculateLevel(int $experience): int
    {
        for ($level = self::MAX_LEVEL; $level >= 1; $level--) {
            if ($experience >= $this->requiredExperience($level)) {
                return $level;
            }
        }

        return 1;
    }

    /**
     * レベルから現在の進化段階を計算
     */
    public function calculateStage(int $level): string
    {
        return match (true) {
            $level >= 100 => '6',
            $level >= 75 => '5',
            $level >= 50 => '4',
            $level >= 30 => '3',
            $level >= 15 => '2',
            $level >= 5 => '1',
            default => 'egg',
        };
    }


    /**
     * 次のレベルまでに必要なEXP
     */
    public function experienceToNextLevel(Dopa $dopa): int
    {
        if ($dopa->level >= self::MAX_LEVEL) {
            return 0;
        }

        return max(
            0,
            $this->requiredExperience($dopa->level + 1) - $dopa->experience
        );
    }

    /**
     * 現在レベル内での進捗率
     */
    public function progressPercentage(Dopa $dopa): float
    {
        if ($dopa->level >= self::MAX_LEVEL) {
            return 100;
        }

        $current = $this->requiredExperience($dopa->level);
        $next = $this->requiredExperience($dopa->level + 1);

        if ($next <= $current) {
            return 100;
        }

        return round(
            (($dopa->experience - $current) / ($next - $current)) * 100,
            1
        );
    }

    /**
     * EXP・レベル・進化段階をまとめて更新
     */
    private function applyExperience(Dopa $dopa, int $amount): void
    {
        $dopa->experience += $amount;

        $dopa->level = $this->calculateLevel(
            $dopa->experience
        );

        $dopa->stage = $this->calculateStage(
            $dopa->level
        );

        $dopa->save();
    }

    /**
     * EXPを直接付与
     */
    public function addExperience(Dopa $dopa, int $amount): Dopa
    {
        if ($amount <= 0) {
            return $dopa;
        }

        $this->applyExperience($dopa, $amount);

        return $dopa->refresh();
    }

    /**
     * 行動に応じてEXPを付与し、履歴も保存
     */
    public function reward(
        Dopa $dopa,
        string $action,
        ?Model $source = null
    ): Dopa {
        $amount = self::EXP[$action] ?? 0;

        if ($amount <= 0) {
            return $dopa;
        }

        DB::transaction(function () use ($dopa, $action, $amount, $source) {
            $dopa->experienceLogs()->create([
                'amount' => $amount,
                'action' => $action,
                'source_type' => $source?->getMorphClass(),
                'source_id' => $source?->getKey(),
            ]);

            $this->applyExperience($dopa, $amount);
        });

        return $dopa->refresh();
    }

    /**
     * ユーザーのドパを取得
     * まだ存在しなければ作成
     */
    public function getOrCreate(User $user): Dopa
    {
        return $user->dopa()->firstOrCreate(
            [],
            [
                'name' => 'マイドパ',
                'experience' => 0,
                'level' => 1,
                'stage' => 'egg',
                'type' => 'normal',
            ]
        );
    }

    /**
     * 次の進化レベルを取得
     */
    public function nextEvolutionLevel(int $level): ?int
    {
        return match (true) {
            $level < 5 => 5,
            $level < 15 => 15,
            $level < 30 => 30,
            $level < 50 => 50,
            $level < 75 => 75,
            $level < 100 => 100,
            default => null,
        };
    }

    /**
     * 次の進化まであと何レベルか
     */
    public function levelsToNextEvolution(int $level): int
    {
        $nextLevel = $this->nextEvolutionLevel($level);

        if ($nextLevel === null) {
            return 0;
        }

        return $nextLevel - $level;
    }
}