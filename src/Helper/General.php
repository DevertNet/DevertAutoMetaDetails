<?php declare(strict_types=1);

namespace Devert\AutoMetaDetails\Helper;

class General
{
    public function getPhrase(array $phrases, int $increment_id)
    {
        $phrases = array_values($phrases);

        if ($phrases)
        {
            $index = $increment_id % count($phrases);

            if (isset($phrases[$index]) && $phrases[$index])
            {
                return $phrases[$index];
            }
        }

        return false;
    }
}