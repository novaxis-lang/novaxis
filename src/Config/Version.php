<?php
namespace Novaxis\Config;

use Novaxis\Config\Utils;

class Version {
	const CURRENT_VERSION = Utils::VERSION;
	const SEMVER_PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/';

	public static function isValid(string $version): bool {
		return (bool) preg_match(self::SEMVER_PATTERN, $version);
	}

	public static function compare(string $version1, string $version2): int {
		return version_compare($version1, $version2);
	}

	public static function isAtLeast(string $version): bool {
		return self::compare(self::CURRENT_VERSION, $version) >= 0;
	}

	public static function featureAvailable(string $featureVersion): bool {
		return self::isAtLeast($featureVersion);
	}
}