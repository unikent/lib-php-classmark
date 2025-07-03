<?php
/**
 * Classmark helper methods.
 *
 * @copyright  2016 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace unikent\Classmark;

/**
 * Classmark representation.
 */
class Classmark
{
    /**
     * Classmark subject.
     *
     * @var string
     */
    private $subject;

    /**
     * Classmark subdivision.
     *
     * @var string
     */
    private $subdivision;

    /**
     * Construct a new classmark object.
     */
    public function __construct($subject, $subdivision = '')
    {
        $this->subject = $subject;
        $this->subdivision = $subdivision;
    }

    /**
     * Parse a classmark object.
     */
    public static function parse($classmark)
    {
    
        // Validate the classmark
        if($classmark == null || $classmark == '' || empty($classmark)) {
            throw new \InvalidArgumentException('Invalid classmark provided for parse - No value provided;');
        }
    
        // Setup our variables.
        $subject = null;
        $subdivision = null;
    
        $classmark = strtoupper($classmark); // Uppercase our $classmark string
    
        // Does the classmark start with a number?
        if (is_numeric(substr($classmark, 0, 1))) {
            throw new \InvalidArgumentException('Invalid classmark provided for parse - Value begins with a number (' . $classmark . ');');
        }
    
        // SUBJECTS
        // ----------------------------------------------------------------------------------
    
        $subject = substr($classmark, 0, $number_index);    
        $subdivision = trim(substr($classmark, strlen($subject)));
    
        // Find and remove any decimal or space on the subject value
        if (preg_match('/(\.|\s)/', $subject, $matches, PREG_OFFSET_CAPTURE)) {
            $subject = str_replace([' ','.'], '', $subject);
        }
    
        // Check if first letter is a valid classmark
        // If character is I, O, W, or X = Return false (Invalid classmark)
        if (preg_match('/^[IOWX]/', $subject)) {
            throw new \InvalidArgumentException('Invalid classmark provided for parse - First subject letter ' . $subject[0] . ' is invalid (' . $classmark . ');');
        }
    
        // SUBDIVISIONS
        // ----------------------------------------------------------------------------------
    
        // Search for first single space in the remaining string
        preg_match('/\s/', $subdivision, $matches, PREG_OFFSET_CAPTURE);
        if (!empty($matches)) {
            // Remove all characters from that index to the end of the string
            $index = $matches[0][1];
            $subdivision = substr($subdivision, 0, $index);
        }
    
        // Check if first character is a space or decimal
        if (preg_match('/^(\.|\s)/', $subdivision)) {
            $subdivision = substr($subdivision, 1); // Remove the character
        }
    
        if (empty($subject) || empty($subdivision)) {
            throw new \InvalidArgumentException('Invalid classmark provided for parse - Parsing returned an empty value for subject and/or sub-subject (' . $classmark . ');');
        }
        
        return new static($subject, $subdivision);

    }

    /**
     * Return the subject.
     */
    public function get_subject()
    {
        return $this->subject;
    }

    /**
     * Return the subdivision.
     */
    public function get_subdivision()
    {
        return $this->subdivision;
    }

    /**
     * Comparison.
     * Returns 0 if we match, 1 if we are greater than $classmark or -1 if we are less than $classmark.
     */
    public function compareTo($classmark)
    {
        if (!($classmark instanceof self)) {
            throw new \InvalidArgumentException('Invalid classmark provided for comparison.');
        }

        // If subjects are equal we base it on subdivision.
        if ($this->subject == $classmark->subject) {
            // If there is a dot, split there and compare pieces.
            if (strpos($this->subdivision, '.') !== false || strpos($classmark->subdivision, '.') !== false) {
                $pieces = explode('.', trim($this->subdivision, '.\t\n\r'));
                $otherpieces = explode('.', trim($classmark->subdivision, '.\t\n\r'));

                foreach ($pieces as $i => $piece) {
                    if (!isset($otherpieces[$i]) || $piece == $otherpieces[$i]) {
                        continue;
                    }

                    if ($piece > $otherpieces[$i]) {
                        return 1;
                    } else {
                        return -1;
                    }
                }
            }

            return $this->subdivision == $classmark->subdivision ? 0 : ($this->subdivision > $classmark->subdivision ? 1 : -1);
        }

        return $this->subject == $classmark->subject ? 0 : ($this->subject > $classmark->subject ? 1 : -1);
    }

    /**
     * String representation.
     */
    public function __toString()
    {
        return trim("{$this->subject}{$this->subdivision}");
    }
}
