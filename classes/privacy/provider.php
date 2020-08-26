<?php
namespace block_equella_search\privacy;

class provider implements \core_privacy\local\metadata\null_provider {
    // this plugin does not store any personal user data.
    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}
