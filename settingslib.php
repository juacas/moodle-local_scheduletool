<?php
namespace local_scheduletool;
class admin_setting_configmultiselect_autocomplete extends \admin_setting_configmultiselect {
    /** @var boolean $tags Should we allow typing new entries to the field? */
    protected $tags = false;
    /** @var string $ajax Name of an AMD module to send/process ajax requests. */
    protected $ajax = '';
    /** @var string $placeholder Placeholder text for an empty list. */
    protected $placeholder = '';
    /** @var bool $casesensitive Whether the search has to be case-sensitive. */
    protected $casesensitive = false;
    /** @var bool $showsuggestions Show suggestions by default - but this can be turned off. */
    protected $showsuggestions = true;
    /** @var string $noselectionstring String that is shown when there are no selections. */
    protected $noselectionstring = '';

    /**
     * Returns XHTML select field and wrapping div(s)
     *
     * @see output_select_html()
     *
     * @param string $data the option to show as selected
     * @param string $query
     * @return string XHTML field and wrapping div
     */
    public function output_html($data, $query='') {
        global $PAGE;

        $html = parent::output_html($data, $query);

        if ($html === '') {
            return $html;
        }

        $this->placeholder = get_string('search');

        $params = array('#' . $this->get_id(), $this->tags, $this->ajax,
            $this->placeholder, $this->casesensitive, $this->showsuggestions, $this->noselectionstring);

        // Load autocomplete wrapper for select2 library.
        $PAGE->requires->js_call_amd('core/form-autocomplete', 'enhance', $params);

        return $html;
    }
}
class admin_settings_coursecat_multiselect extends admin_setting_configmultiselect_autocomplete {
    /**
     * Calls parent::__construct with specific arguments
     */
    public function __construct($name, $visiblename, $description, $defaultsetting = null) {
        parent::__construct($name, $visiblename, $description, $defaultsetting, $choices = null);
    }

    /**
     * Load the available choices for the select box
     *
     * @return bool
     */
    public function load_choices() {
        if (is_array($this->choices)) {
            return true;
        }
        $this->choices = core_course_category::make_categories_list('', 0, ' / ');
        return true;
    }
}