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
     * Classmark author.
     *
     * @var string
     */
    private $author;

    /**
     * Classmark prefix.
     *
     * @var string
     */
    private $prefix;

    /**
     * Construct a new classmark object.
     */
    public function __construct($subject, $subdivision = '', $author = '', $prefix = '')
    {
        $this->subject = $subject;
        $this->subdivision = $subdivision;
        $this->author = $author;
        $this->prefix = $prefix;
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
        $sub_subject = null;
    
        $classmark = strtoupper($classmark); // Uppercase our $classmark string
    
        // Does the classmark start with a number?
        if (is_numeric(substr($classmark, 0, 1))) {
            throw new \InvalidArgumentException('Invalid classmark provided for parse - Value begins with a number (' . $classmark . ');');
        }
    
        // PREFIX
        // ----------------------------------------------------------------------------------

        // Commented out on 2023-10-30 as it may not be relevant going forward.
    
        // // If the string start with 'FOL', 'LRG', or 'PER'
        // if(substr($classmark, 0, 3) == 'FOL' || substr($classmark, 0, 3) == 'LRG' || substr($classmark, 0, 3) == 'PER') {
    
        //     // If yes - Remove the prefix
        //     $classmark = substr($classmark, 3); // Remove the prefix
            
        //     // Check if first character is a space
        //     if(substr($classmark, 0, 1) == ' ') {
        //         $classmark = substr($classmark, 1); // Remove the space
        //     }
    
        // }
    
        // // If there is single prefix letter ('F', 'L', 'P', or 'Q') followed by a space / decimal and another letter (If followed by a number then its is valid)
        // $prefixes = ['F', 'L', 'P', 'Q'];
        // if (in_array(substr($classmark, 0, 1), $prefixes) && in_array($classmark[1], [' ', '.'])) {
        //     $classmark = substr($classmark, 2); // Remove the character and the space / decimal
        // }
    
        // // Check if the first two characters are either 'ff', 'll', 'pp', or 'qq'
        // if (preg_match('/^ff|^ll|^pp|^qq/i', $classmark)) {
        //     // If yes - Remove the first character and continue
        //     $classmark = substr($classmark, 1); // Remove the first character
        // }
    
        // // How many letters are there at the start?
        // $number_index = 0; // Default value
        // if (preg_match('/\d/', $classmark, $matches, PREG_OFFSET_CAPTURE)) {
        //     $number_index = $matches[0][1];
        // }
    
        // SUBJECTS
        // ----------------------------------------------------------------------------------
    
        $subject = substr($classmark, 0, $number_index);    
        $sub_subject = trim(substr($classmark, strlen($subject)));
    
        // Find and remove any decimal or space on the subject value
        if (preg_match('/(\.|\s)/', $subject, $matches, PREG_OFFSET_CAPTURE)) {
            $subject = str_replace([' ','.'], '', $subject);
        }
    
        // Check if first letter is a valid classmark
        // If character is I, O, W, or X = Return false (Invalid classmark)
        if (preg_match('/^[IOWX]/', $subject)) {
            throw new \InvalidArgumentException('Invalid classmark provided for parse - First subject letter ' . $subject[0] . ' is invalid (' . $classmark . ');');
        }
    
        // SUB-SUBJECTS
        // ----------------------------------------------------------------------------------
    
        // Search for first single space in the remaining string
        preg_match('/\s/', $sub_subject, $matches, PREG_OFFSET_CAPTURE);
        if (!empty($matches)) {
            // Remove all characters from that index to the end of the string
            $index = $matches[0][1];
            $sub_subject = substr($sub_subject, 0, $index);
        }
    
        // Check if first character is a space or decimal
        if (preg_match('/^(\.|\s)/', $sub_subject)) {
            $sub_subject = substr($sub_subject, 1); // Remove the character
        }
    
        if (empty($subject) || empty($sub_subject)) {
            throw new \InvalidArgumentException('Invalid classmark provided for parse - Parsing returned an empty value for subject and/or sub-subject (' . $classmark . ');');
        }
        
        return new static($subject . ' ' . $sub_subject);

    }

    /**
     * Return the author.
     */
    public function get_author()
    {
        return $this->author;
    }

    /**
     * Return the prefix.
     */
    public function get_prefix()
    {
        return $this->prefix;
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
        return trim("{$this->prefix}{$this->subject}{$this->subdivision}{$this->author}");
    }
}
