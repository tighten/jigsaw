<?php

namespace Tests;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

trait Haiku
{
    protected static array $adjectives = ['aged', 'amiable', 'ancient', 'balmy', 'beautiful', 'billowing', 'blessed', 'bold', 'bountiful', 'breezy', 'bubbling', 'calm', 'celestial', 'clean', 'cold', 'colorful', 'colossal', 'crimson', 'curved', 'damp', 'deep', 'divine', 'exquisite', 'fancy', 'fathomless', 'floral', 'fragrant', 'frosty', 'gentle', 'gorgeous', 'graceful', 'harmonious', 'hidden', 'hollow', 'holy', 'icy', 'indigo', 'jubilant', 'limitless', 'lingering', 'lively', 'mellow', 'merciful', 'mirthful', 'misty', 'moonlit', 'mythic', 'quaint', 'polished', 'precious', 'purple', 'red', 'resilient', 'royal', 'scenic', 'silent', 'snowy', 'solitary', 'sparkling', 'stunning', 'summer', 'smooth', 'tall', 'twilight', 'unwavering', 'warm', 'wandering', 'weathered', 'whispering', 'wispy', 'zealous'];

    protected static array $nouns = ['abyss', 'atoll', 'aurora', 'autumn', 'badlands', 'beach', 'breeze', 'briars', 'butterfly', 'brook', 'canopy', 'canyon', 'cavern', 'chasm', 'cliff', 'cloud', 'cove', 'crater', 'creek', 'darkness', 'dawn', 'desert', 'dew', 'dream', 'dusk', 'dust', 'farm', 'feather', 'fern', 'field', 'fire', 'firefly', 'flowers', 'fog', 'forest', 'frost', 'galaxy', 'garden', 'geyser', 'glade', 'grass', 'grove', 'hamlet', 'haze', 'hill', 'hurricane', 'iceberg', 'king', 'lagoon', 'lake', 'leaf', 'meadow', 'mist', 'moon', 'morning', 'moss', 'mountain', 'night', 'oasis', 'ocean', 'peak', 'pebble', 'pine', 'plateau', 'pond', 'rain', 'reef', 'reserve', 'resonance', 'river', 'sanctuary', 'sands', 'sea', 'shadow', 'shelter', 'silence', 'snowflake', 'sound', 'spring', 'star', 'storm', 'stream', 'summer', 'summit', 'sun', 'sunrise', 'sunset', 'surf', 'thunder', 'temple', 'truth', 'tundra', 'valley', 'waterfall', 'wave', 'wildflower', 'willow', 'winds', 'winter'];

    public static function haiku()
    {
        return implode('-', [Arr::random(static::$adjectives), Arr::random(static::$nouns), Str::random(4)]);
    }
}
