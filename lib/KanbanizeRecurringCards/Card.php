<?php

namespace KanbanizeRecurringCards;

/**
 * A definition of a recurring card.
 */
class Card {

  protected static $optional_elements = array(
    'title',
    'description',
    'priority',
    'assignee',
    'color',
    'size',
    'tags',
    'deadline',
    'extlink',
    'template',
    'subtasks',
    'column',
    'lane',
    'position',
    'exceedingreason',
  );
  protected static $board_custom_fields = array();

  public function __construct(array $data) {
    // hour
    $options = array('min_range' => 0, 'max_range' => 23);
    if (empty($data['hour']) || filter_var($data['hour'], FILTER_VALIDATE_INT) === false) {
      throw new \Exception("hour must be an integer between 0 and 23. ".$data['hour']." given.");
    }
    // start_date
    if (empty($data['start_date']) || !preg_match('/^\d\d\d\d-\d\d-\d\d$/', $data['start_date'])) {
      throw new \Exception("start_date must be a valid date string in the YYYY-MM-DD format. ".$data['start_date']." given.");
    }
    // board
    $options = array('min_range' => 0);
    if (empty($data['board']) || filter_var($data['board'], FILTER_VALIDATE_INT) === false) {
      throw new \Exception("board must be an integer between 0 and 23. ".$data['board']." given.");
    }
    // recurrence
    if (empty($data['recurrence']) || !preg_match('/FREQ=.+/i', $data['recurrence'])) {
      throw new \Exception("recurrence an Recurrence-Rule supported by https://github.com/simshaun/recurr as defined in https://tools.ietf.org/html/rfc2445#section-4.3.10 , for example: 'FREQ=WEEKLY;BYDAY=MO'. '".$data['recurrence']."' given.");
    }
    $this->data = $data;
  }

  public function recurrsBetween(\DateTime $after, \DateTime $before) {
    $my_start = new \DateTime($this->data['start_date'].' '.$this->data['hour'].':00:00');
    $my_end = new \DateTime($this->data['start_date'].' '.$this->data['hour'].':30:00');
    try {
      $recurrRule = new \Recurr\Rule($this->data['recurrence'], $my_start, $my_end);
      $between = new \Recurr\Transformer\Constraint\BetweenConstraint($after, $before, true);
      $transformer = new \Recurr\Transformer\ArrayTransformer();
      $recurrences = $transformer->transform($recurrRule, $between);
    } catch (\Exception $e) {
      throw new \Exception("Error creating recurrence rule from '".$this->data['recurrence']."': ".$e->getMessage());
    }
    return (count($recurrences) > 0);
  }

  public function addToKanbanize(\EtuDev_KanbanizePHP_API $kanbanize) {
    $data = array();
    foreach (self::$optional_elements as $key) {
      if (!empty($this->data[$key])) {
        $data[$key] = $this->data[$key];
      }
    }
    foreach ($this->getBoardCustomFields($kanbanize, $this->data['board']) as $key) {
      if (!empty($this->data[$key])) {
        $data[$key] = $this->data[$key];
      }
    }
    return $kanbanize->createNewTask($this->data['board'], $data);
  }

  protected function getBoardCustomFields(\EtuDev_KanbanizePHP_API $kanbanize, $board_id) {
    if (!isset(self::$board_custom_fields[$board_id])) {
      self::$board_custom_fields[$board_id] = array();
      try {
        $settings = $kanbanize->getBoardSettings($board_id);
        foreach ($settings['customFields'] as $field) {
          self::$board_custom_fields[$board_id][] = $field['name'];
        }
      } catch (\Exception $e) {
        // Ignore missing board settings, we'll just skip their fields.
      }
    }
    return self::$board_custom_fields[$board_id];
  }
}
