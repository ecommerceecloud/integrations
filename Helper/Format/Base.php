<?php

namespace Ecloud\Integrations\Helper\Format;

class Base
{
	const CSV_TMP_ATTRIBUTE_HEADER_PREFIX = "_attr_header_";
	const CSV_PRODUCT_DISABLED_STATUS = 2;



	/**
	 * Get the temporal attribute header, used before replacing it for the attribute creation header
	 * @param string $attributeCode the attribute code for the header
	 * @return string
	 */
	public static function tmpAttributeHeader($attributeCode)
	{
		return self::CSV_TMP_ATTRIBUTE_HEADER_PREFIX . $attributeCode;
	}

	/**
	 * Returns the attribute code of an attribute creation temporal header
	 * @param string $tmpHeader the temporal header
	 */
	public static function attributeCodeFromTmpHeader($tmpHeader)
	{
		return str_replace(self::CSV_TMP_ATTRIBUTE_HEADER_PREFIX, "", $tmpHeader);
	}

	/**
	 * Get the full attribute creation header
	 * @param string $attributeCode code of the attribute to create
	 * @param string $attributeLabel label of the attribute to create
	 * @param string $defaultValue default value for the attribute
	 * @param string $attributeType type of the attribute to create
	 */
	public static function attributeCreationHeader($attributeCode, $attributeLabel, $defaultValue, $attributeType)
	{
		return "attribute|" .
			"attribute_code:$attributeCode|" .
			"frontend_input:$attributeType|" .
			"is_required:0|" .
			"is_global:1|" .
			"default_value_text:$defaultValue|" .
			"is_unique:0|" .
			"frontend_class:|" .
			"is_used_in_grid:0|" .
			"is_filterable_in_grid:0|" .
			"is_searchable:0|" .
			"search_weight:3|" .
			"is_visible_in_advanced_search:0|" .
			"is_comparable:0|" .
			"is_filterable:0|" .
			"is_filterable_in_search:0|" .
			"position:2|" .
			"is_used_for_promo_rules:0|" .
			"is_html_allowed_on_front:1|" .
			"is_visible_on_front:0|" .
			"used_in_product_listing:0|" .
			"used_for_sort_by:0|" .
			"frontend_label_0:" . $attributeLabel . "|" .
			"frontend_label_1:" . $attributeLabel . "|" .
			"attribute_set:Default";
	}
}
