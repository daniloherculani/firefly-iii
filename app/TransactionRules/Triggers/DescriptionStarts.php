<?php
/**
 * DescriptionStarts.php
 * Copyright (c) 2017 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\TransactionRules\Triggers;

use FireflyIII\Models\TransactionJournal;
use Log;

/**
 * Class DescriptionStarts.
 */
final class DescriptionStarts extends AbstractTrigger implements TriggerInterface
{
    /**
     * A trigger is said to "match anything", or match any given transaction,
     * when the trigger value is very vague or has no restrictions. Easy examples
     * are the "AmountMore"-trigger combined with an amount of 0: any given transaction
     * has an amount of more than zero! Other examples are all the "Description"-triggers
     * which have hard time handling empty trigger values such as "" or "*" (wild cards).
     *
     * If the user tries to create such a trigger, this method MUST return true so Firefly III
     * can stop the storing / updating the trigger. If the trigger is in any way restrictive
     * (even if it will still include 99.9% of the users transactions), this method MUST return
     * false.
     *
     * @param null $value
     *
     * @return bool
     */
    public static function willMatchEverything($value = null)
    {
        if (null !== $value) {
            $res = '' === strval($value);
            if (true === $res) {
                Log::error(sprintf('Cannot use %s with "" as a value.', self::class));
            }

            return $res;
        }

        Log::error(sprintf('Cannot use %s with a null value.', self::class));

        return true;
    }

    /**
     * Returns true when description starts with X
     *
     * @param TransactionJournal $journal
     *
     * @return bool
     */
    public function triggered(TransactionJournal $journal): bool
    {
        $description = strtolower($journal->description ?? '');
        $search      = strtolower($this->triggerValue);

        $part = substr($description, 0, strlen($search));

        if ($part === $search) {
            Log::debug(sprintf('RuleTrigger DescriptionStarts for journal #%d: "%s" starts with "%s", return true.', $journal->id, $description, $search));

            return true;
        }

        Log::debug(sprintf('RuleTrigger DescriptionStarts for journal #%d: "%s" does not start with "%s", return false.', $journal->id, $description, $search));

        return false;
    }
}
