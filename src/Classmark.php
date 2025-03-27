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

        // Validate the classmark.
        if (!is_string($classmark) || !preg_match('/^([a-z\ ]*[A-Z]{1,2}[A-Za-z0-9\.\ ]*)$/', $classmark)) {
            throw new \InvalidArgumentException('Invalid classmark provided for parse.');
        }

        // Setup our variables.
        $subject = '';
        $sub_subject = '';
    
        // Check the first character and if it is a lowercase 'f' or 'q' then remove it
        if (strlen($classmark) > 1 && preg_match('/(^f|^q)/', $classmark)) {
            $classmark = substr($classmark, 1);
        }
    
        // Uppercase our $classmark string
        $classmark = strtoupper($classmark);
    
        // Check first 2 characters and if they are 'FF' or 'QQ' then remove the first character
        // This relates to when there is a 'f' (for folio) or 'q' (for quarto) at the start of the classmark. We will never have a classmark that has 2 'f's or 2 'q's at the start.
        if (preg_match('/^FF/', $classmark) || preg_match('/^QQ/', $classmark)) {
            $classmark = substr($classmark, 1);
        }
        
        // Check if the string has 1 or 2 alpha characters at the start and then store as $subject
        if (preg_match('/^[A-Z][A-Z]/', $classmark)) {
            $subject = substr($classmark, 0, 2);
        } else {
            $subject = substr($classmark, 0, 1);
        }
    
        // Trim these characters from the $classmark string and store as $sub_subject
        $sub_subject = trim(substr($classmark, strlen($subject)));
    
        // If first character is a decimal or space remove it
        if (preg_match('/(^\.|^\s)/', $sub_subject)) {
            $sub_subject = trim(substr($sub_subject, 1));
        }
    
        // Find index of first space
        preg_match('/\s/', $sub_subject, $matches, PREG_OFFSET_CAPTURE);
        if (!empty($matches)) {
            // Remove all characters from that index to the end of the string
            $index = $matches[0][1];
            $sub_subject = substr($sub_subject, 0, $index);
        }
    
        // Check last character for a decimal or space
        preg_match('/(\.|\s)$/', $sub_subject, $matches, PREG_OFFSET_CAPTURE);
        if (!empty($matches)) {
            $index = $matches[0][1];
            // If it is a space or a decimal, remove it
            $sub_subject = substr($sub_subject, 0, $index);
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
