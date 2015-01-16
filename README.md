# event-espresso-4-custom-export
I needed to tweak the export of attendees from the WordPress plugin Event Espresso. As the built in function was really far from what I needed (and changing it would mean all changes lost upon upgrade), I wrote a custom export. This is adjusted to my needs, so it might not work fully out of the box for anyone else, but it might be a good starting point, making it easier to get something up and running for other people needing something else than the standard Event Espresso export.

The setup is kind of strange, but it does what it needs to. Feel free to create separate branches and improve on this. This code is provided as is.

## How to use

### Step 1
Download the .php-file in this project. Place it in your WordPress theme folder.

### Step 2
Create a page from the WordPress admin. In this example, I'm naming it "Event Espresso Export", giving it the slug event-espresso-export. In the template selection dropdown, assign it the Event Espresso Attendee Export template (added in step 1).

### Step 3
Put the following code in your functions.php. This will add an export button to the event espresso attendee list page, and hide the original export button (to not confuse users by displaying two export buttons).

NOTE: If you name your page something else in step 2, change the href in the button link!

```
// Add export button to event espresso list attendee page
add_action('admin_footer', 'my_custom_export_attendees_button');
function my_custom_export_attendees_button() {
    $screen = get_current_screen();
    if ( $screen->id != "event-espresso_page_espresso_registrations" )   // Only add to attendees page
        return;

    if (isset($_REQUEST['event_id'])) $event_id = esc_attr($_REQUEST['event_id']);

        $knapp = '<a target=\"_blank\" class=\"button-primary\" id=\"registrations-csv-report\" href=\"/event-espresso-export/?event_id=' . $event_id . '\">Download attendee list</a>';

?>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
                $(".list-table-bottom-buttons").before("<div class=\"alignleft actions\"><?php echo $knapp ?></div>");
                $(".list-table-bottom-buttons #registrations-csv-report").css( "display", "none" );
        });
    </script>

<?php
}
```
