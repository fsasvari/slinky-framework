<?php

namespace Slinky\Validation;

use Slinky\Database\Database;

use Slinky\Validation\ValidateFactory;
use Slinky\Exception\Core\MethodNotFoundException;

class Validator
{
	private $db;
	private $validateFactory;

	private $data = array();
	private $rules = array();
	private $messages = array();

	private $error = false;

	private $messages_default = array(
		'required' => 'The {field} field is required.',
		'string' => 'The {field} field must be a string.',
		'int' => 'The {field} field must be a integer.',
		'decimal' => 'The {field} field must be a decimal.',
		'number' => 'The {field} field must be a number (integer or decimal).',
		'accepted' => 'The {field} field must be accepted.',
		'array' => 'The {field} field must be array.',
		'min' => 'The {field} field must be minimum {rule_value}.',
		'max' => 'The {field} field must be maximum {rule_value}.',
		'range' => 'The {field} field must be between {rule_value_min} and {rule_value_max}.',
		'minlength' => 'The {field} field must be minimum {rule_value} characters long.',
		'maxlength' => 'The {field} field must be maximum {rule_value} characters long.',
		'rangelength' => 'The {field} field must be between {rule_value_min} and {rule_value_max} characters long.',
		'equalto' => '"{value}" is not equal to {rule_value} field.',
		'dateformat' => '"{value}" is not valid date format. Must be in {rule_value} format.',
		'email' => '"{value}" is not valid email address.',
		'url' => '"{value}" is not valid url.',
		'mimes' => 'The {field} field must be in "{values}" extensions.',
		'unique' => 'The {field} field with "{value}" value already exists.',
		'exists' => 'The {field} field with "{value}" value does not exists.',
		'size' => 'The {field} field must be maximum {rule_value} size.'
	);


	/**
	 * @param ValidateFactory $validate_factory
	 * @param Database $database
	 * @return void
	 */
	public function __construct(ValidateFactory $validate_factory, Database $database)
	{
		$this->validateFactory = $validate_factory;
		$this->db = $database;
	}


	/**
	 * @param array $data
	 * @param array $rules required|min:x|max:y|range:x,y|minlegth:x|maxlength:y|rangelength:x,y|email|url|equalto
	 * @param array $messages
	 * @return void
	 */
	public function make($data, $rules, $messages = array())
	{
		$this->reset();

		$this->data = $data;
		$this->setRules($rules);
		$this->messages = $messages;

		$this->validate();

		return $this->validateFactory->build($this->error, $this->messages);
	}


	/**
	 * Reset error status and messages
	 *
	 * @return void
	 */
	private function reset()
	{
		$this->error = false;
		$this->messages = [];
	}


	/**
	 * Set rules
	 *
	 * @param array $fields
	 * @return void
	 */
	private function setRules($fields)
	{
		$fields_new = [];

		foreach ($fields as $field => $rules) {
			$fields_new[$field] = $this->getRules($rules);
		}

		$this->rules = $fields_new;
	}


	/**
	 * Get rules from array or string separated with |
	 *
	 * @param string/array
	 * @return array
	 */
	private function getRules($rules)
	{
		$rules_new = [];

		if (!is_array($rules)) {
			$rules = explode('|', $rules);
		}

		foreach ($rules as $values) {
			$rule_and_values = $this->getRuleAndValues($values);
			$rules_new[$rule_and_values[0]] = $this->getValues($rule_and_values[1]);
		}

		return $rules_new;
	}


	/**
	 * Get rule and values (value1, value2) as array
	 *
	 * @param array|string $values
	 * @return array
	 */
	private function getRuleAndValues($values)
	{
		if (!is_array($values)) {
			$values = explode(':', $values);
		}

		if (count($values) < 2) {
			$values[1] = true;
		}

		return $values;
	}


	/**
	 * Get values as array
	 *
	 * @param array|string $values
	 * @return array
	 */
	private function getValues($values)
	{
		if (!is_array($values)) {
			$values = explode(',', $values);
		}

		return $values;
	}


	/**
	 * Validate data with selected rules
	 *
	 * @return void
	 */
	private function validate()
	{
		// fetch fields
		foreach ($this->rules as $field => $rules) {
			// fetch rules of specific field
			foreach ($rules as $rule) {
				$validate_method = $this->getValidateMethod($rule['rule']);

				if (isset($rule['value2'])) {
					$error = $this->{$validate_method}($field, $rule['value1'], $rule['value2']);
				} elseif (isset($rule['value1'])) {
					$error = $this->{$validate_method}($field, $rule['value1']);
				} else {
					$error = $this->{$validate_method}($field);
				}

				if ($error) {
					$this->setError($rule, $field);
				}
			}
		}
	}


	/**
	 * Return validation method
	 *
	 * @param string $rule
	 * @return string
	 */
	private function getValidateMethod($rule)
	{
		$validate_method = 'validate' . str_studly_caps($rule);

		if (!method_exists($this, $validate_method)) {
			throw new MethodNotFoundException('Validation method does not exists: ' . $validate_method);
		}

		return $validate_method;
	}


	/**
	 * @param string $rule
	 * @param string $field
	 * @set bool $error
	 * @set array $error_messages
	 * @return void
	 */
	private function setError($rule, $field)
	{
		$this->error = true;
		if (isset($this->messages[$field][$rule['rule']])) {
			$this->error_messages[$field][] = $this->messages[$field][$rule['rule']];
		} else {
			$this->error_messages[$field][] = $this->getMessageDefault($rule, $field);
		}
	}


	/**
	 * @param array $rule
	 * @param string $field
	 * @return string
	 */
	private function getMessageDefault($rule, $field)
	{
		$from = array (
			'{field}',
			'{value}',
			'{rule_value}',
			'{rule_value_min}',
			'{rule_value_max}'
		);
		$to = array (
			$field,
			$this->data[$field],
			isset($rule['value1']) ? $rule['value1'] : '',
			isset($rule['value1']) ? $rule['value1'] : '',
			isset($rule['value2']) ? $rule['value2'] : ''
		);

		return str_replace($from, $to, $this->messages_default[$rule['rule']]);
	}


	/**
	 * @param string $field
	 * @return bool
	 */
	private function validateRequired($field)
	{
		if (!isset($this->data[$field]) || !$this->data[$field]) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Validate minimum integer value
	 *
	 * @param string $field
	 * @param int $min
	 * @return bool
	 */
	private function validateMin($field, $min)
	{
		if (isset($this->data[$field]) && $this->data[$field] < $min) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Validate maximum integer value
	 *
	 * @param string $field
	 * @param int $max
	 * @return bool
	 */
	private function validateMax($field, $max)
	{
		if (isset($this->data[$field]) && $this->data[$field] > $max) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Validate minimum and maximum integer value
	 *
	 * @param string $field
	 * @param int $min
	 * @param int $max
	 * @return bool
	 */
	private function validateRange($field, $min, $max)
	{
		if (isset($this->data[$field]) && ($this->data[$field] < $min || $this->data[$field] > $max)) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Validate minimum string length
	 *
	 * @param string $field
	 * @param int $min
	 * @return bool
	 */
	private function validateMinLength($field, $min)
	{
		if (isset($this->data[$field]) && str_length($this->data[$field]) < $min) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Validate maximum string length
	 *
	 * @param string $field
	 * @param int $max
	 * @return bool
	 */
	private function validateMaxLength($field, $max)
	{
		if (isset($this->data[$field]) && str_length($this->data[$field]) > $max) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * Validate minimum and maximum string length
	 *
	 * @param string $field
	 * @param int $min
	 * @param int $max
	 * @return bool
	 */
	private function validateRangeLength($field, $min, $max)
	{
		if (isset($this->data[$field]) && (str_length($this->data[$field]) < $min || str_length($this->data[$field]) > $max)) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * @param string $field
	 * @return bool
	 */
	private function validateEmail($field)
	{
		if (filter_var($this->data[$field], FILTER_VALIDATE_EMAIL) === false) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * @param string $field
	 * @return bool
	 */
	private function validateUrl($field)
	{
		if (filter_var($this->data[$field], FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED) === false) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * @param string $field
	 * @param string $field_equal_to
	 * @return bool
	 */
	private function validateEqualTo($field, $field_equal_to)
	{
		if ($this->data[$field] != $this->data[$field_equal_to]) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * @param string $field
	 * @param string $date_format
	 * @return bool
	 */
	private function validateDateFormat($field, $date_format)
	{
		$date = date_parse_from_format($date_format, $this->data[$field]);

		if (isset($this->data[$field]) && $date['error_count'] > 0) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * @param string $field
	 * @param string $table
	 * @return bool
	 */
	private function validateUnique($field, $table)
	{
		if ($this->data[$field] && $this->getDatabaseData($field, $table)) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * @param string $field
	 * @param string $table
	 * @return bool
	 */
	private function validateExists($field, $table)
	{
		if ($this->data[$field] && !$this->getDatabaseData($field, $table)) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * @param string $field
	 * @param string $table
	 * @return mixed
	 */
	private function getDatabaseData($field, $table)
	{
		$query = 'SELECT ' . $field . ' FROM ' . $table . ' WHERE ' . $field . ' = :' . $field;

		$bind = array (
			$field => $this->data[$field]
		);

		return $this->db->query($query)->bind($bind)->first();
	}
}
