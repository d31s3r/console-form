<?php

namespace Platformsh\ConsoleForm\Field;

use Symfony\Component\Console\Question\ChoiceQuestion;

class OptionsField extends Field
{
    protected $options = [];
    protected $asChoice = true;

    /**
     * A callback used to calculate dynamic options.
     *
     * The callback accepts an array of values entered previously for other form
     * fields. It returns the new options, as an array.
     *
     * @var callable
     */
    protected $optionsCallback;

    /**
     * {@inheritdoc}
     */
    public function __construct($name, array $config = [])
    {
        parent::__construct($name, $config);
        $this->validators[] = function ($value) {
            return in_array($value, $this->options, true)
                ? true : "$value is not one of: " . implode(', ', $this->options);
        };
    }

    /**
     * {@inheritdoc}
     */
    public function matchesCondition($userValue, $condition)
    {
        return is_array($condition)
          ? in_array($userValue, $condition)
          : $userValue === $condition;
    }

    /**
     * {@inheritdoc}
     */
    public function getAsQuestion()
    {
        if ($this->asChoice) {
            $question = $this->getChoiceQuestion();
        }
        else {
            $question = parent::getAsQuestion();
            $question->setAutocompleterValues($this->options);
        }

        return $question;
    }

    /**
     * {@inheritdoc}
     */
    protected function getChoiceQuestion()
    {
        // Translate the default into an array key.
        $defaultKey = $this->default !== null
            ? array_search($this->default, $this->options, true) : $this->default;

        $question = new ChoiceQuestion(
            $this->name . " (enter a number to choose): ",
            $this->options,
            $defaultKey !== false ? $defaultKey : null
        );
        $question->setMaxAttempts($this->maxAttempts);

        return $question;
    }

    /**
     * {@inheritdoc}
     */
    public function onChange(array $previousValues)
    {
        if (isset($this->optionsCallback)) {
            $callback = $this->optionsCallback;
            $this->options = $callback($previousValues);
        }
    }
}
