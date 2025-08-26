<?php
/**
 * Translate a given key using the current session language.
 * Falls back to English if the translation file for the language is missing.
 * Supports optional sprintf-style placeholders via ...$params.
 *
 * @param string $key : The translation key.
 * @param mixed ...$params : Values to replace placeholders in translation.
 * @return string : Translated string or fallback.
 */
function __($key, ...$params) {
	static $locale = [];

	global $config, $locale_path;

	$language = isset($_SESSION['language'])
		? strtolower($_SESSION['language'])
		: $config['APP_LANGUAGE'];

	$file = "$locale_path/$language/locale.txt";
	$default_file = "$locale_path/en/locale.txt";

	if (!isset($locale[$language])) {
		$locale[$language] = [];

		$path = file_exists($file) ? $file : (file_exists($default_file) ? $default_file : null);

		if ($path) {
			$lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			foreach ($lines as $line) {
				$parts = explode("\t", $line, 2);
				if (count($parts) === 2) {
					$locale[$language][$parts[0]] = $parts[1];
				}
			}
		}
	}

	$text = $locale[$language][$key] ?? '{' . $key . '}';

	if (!empty($params)) {
		return vsprintf($text, $params);
	}

	return $text;
}

/**
 * Converts a string to UTF-8 if it's encoded in a known language-specific encoding.
 *
 * @param string $string : Input string to normalize.
 * @return string : UTF-8 encoded string.
 */
function GetEncodedString($string) {
	$encoding = mb_detect_encoding($string, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
	return mb_convert_encoding($string, 'UTF-8', $encoding ?: 'Windows-1252');
}

/**
 * Reads item names from a language file and returns them
 * as a formatted JavaScript-friendly array string.
 *
 * @return string : JS array as text (used in <script> blocks).
 */
function GetItemNamesJS() {
	global $config, $locale_path;

	$language = isset($_SESSION['language'])
		? strtolower($_SESSION['language'])
		: $config['APP_LANGUAGE'];

	$file = "$locale_path/$language/item_names.txt";
	$items = [];

	if (file_exists($file)) {
		$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach ($lines as $line) {
			list($vnum, $name) = explode("\t", $line);

			$vnum = trim($vnum);
			$name = trim($name);

			if ($name === "LOCALE_NAME") {
				$name = "Exp.";
				$vnum = "exp";
			} elseif ($vnum === "1") {
				$vnum = "gold";
			}

			$items[] = [
				"value" => $vnum,
				"text" => GetEncodedString($name),
				"image" => GetItemIcon($vnum)
			];
		}
	}

	$result = [];

	foreach ($items as $item) {
		$result[] = '{ value: "' . $item["value"] . '", text: "' . $item["text"] . '", image: "' . $item["image"] . '" }';
	}

	return implode(",\n", $result);
}

/**
 * Returns the file path of an item icon based on vnum.
 * Special vnums like "gold" and "exp" have fixed icons.
 *
 * @param string $vnum : The item identifier.
 * @return string : Path to the icon image.
 */
function GetItemIcon($vnum) {
	if ($vnum === "1" || $vnum === "gold") {
		return "share/icon/item/money.png";
	} elseif ($vnum === "exp") {
		return "share/icon/item/exp.png";
	}

	global $item_icon_array;

	if (isset($item_icon_array[$vnum])) {
		return $item_icon_array[$vnum];
	}

	return "share/icon/item/dummy.png";
}

/**
 * Loads item icon list from item_list.txt and caches it.
 * Used to map item vnums to their corresponding icon paths.
 *
 * @return array : Associative array of item icons.
 */
function ReadItemIconList() {
	static $item_icon_array = null;

	if ($item_icon_array !== null) {
		return $item_icon_array;
	}

	global $locale_path;
	$item_icon_array = [];

	$file = "$locale_path/item_list.txt";
	if (file_exists($file)) {
		$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			list($vnum, $type, $icon) = explode("\t", $line);

			$dir = pathinfo($icon, PATHINFO_DIRNAME);
			$name = pathinfo($icon, PATHINFO_FILENAME);

			$item_icon_array[$vnum] = "share/$dir/$name.png";
		}
	}

	return $item_icon_array;
}

/**
 * Retrieves the description of an item based on its vnum.
 * Returns an empty string if no description is found.
 *
 * @param string $vnum : The item identifier.
 * @return string : UTF-8 encoded item description.
 */
function GetItemDescription($vnum)
{
	$desc_array = ReadItemDescriptionList();
	$desc = isset($desc_array[$vnum]) ? $desc_array[$vnum] : "";
	return GetEncodedString($desc);
}

/**
 * Loads item descriptions from the localized itemdesc.txt file and caches them.
 * Each line should contain tab-separated values: vnum, name, description.
 *
 * @return array : Associative array mapping vnum to description.
 */
function ReadItemDescriptionList() {
	static $cache = [];

	global $config;

	$language = isset($_SESSION['language'])
		? strtolower($_SESSION['language'])
		: $config['APP_LANGUAGE'];

	if (isset($cache[$language])) {
		return $cache[$language];
	}

	global $locale_path;
	$item_desc_array = [];

	$file = "$locale_path/$language/itemdesc.txt";
	if (file_exists($file)) {
		$lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		foreach ($lines as $line) {
			list($vnum, $name, $desc) = explode("\t", $line);
			$item_desc_array[$vnum] = $desc;
		}
	}

	$cache[$language] = $item_desc_array;
	return $item_desc_array;
}

/**
 * Returns the URL to the correct DataTables language JSON file
 * based on the current session language.
 *
 * @return string : CDN path to i18n JSON file.
 */
function GetDataTableLanguage() {
	global $config;

	$language = isset($_SESSION['language'])
		? strtolower($_SESSION['language'])
		: $config['APP_LANGUAGE'];

	if (strcmp($language, "ae") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/ar.json";
	} else if (strcmp($language, "cz") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/cs.json";
	} else if (strcmp($language, "de") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/de-DE.json";
	} else if (strcmp($language, "en") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/en-GB.json";
	} else if (strcmp($language, "es") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/es-ES.json";
	} else if (strcmp($language, "fr") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/fr-FR.json";
	} else if (strcmp($language, "gr") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/el.json";
	} else if (strcmp($language, "hu") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/hu.json";
	} else if (strcmp($language, "it") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/it-IT.json";
	} else if (strcmp($language, "pl") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/pl.json";
	} else if (strcmp($language, "pt") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/pt-PT.json";
	} else if (strcmp($language, "ro") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/ro.json";
	} else if (strcmp($language, "ru") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/ru.json";
	} else if (strcmp($language, "tr") == 0) {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/tr.json";
	} else {
		return "//cdn.datatables.net/plug-ins/1.13.2/i18n/en-GB.json";
	}
}
?>
