
add_action('weekly_snapshot_event', 'run_weekly_snapshot');

function run_weekly_snapshot() {

    $now = current_time('timestamp');

    // ΜΟΝΟ Δευτέρα
    if (date('w', $now) != 1) {
        return;
    }

    // protection (να μην τρέχει 2 φορές την ίδια μέρα)
    $last_run = get_option('weekly_snapshot_last_run');
    $today = date('Y-m-d', $now);

    if ($last_run === $today) {return;}

    $page_link = generate_weekly_snapshot_page();

    update_option('weekly_snapshot_last_run', $today);

    // ===== DISCORD WEBHOOK =====
    $webhook_url = 'DISCORD_WEBHOOK';

    $message = [
        "content" =>
        "## 📊 Εβδομαδιαία Σύνοψη\n" .
         $page_link
    ];

    wp_remote_post($webhook_url, [
        'headers' => ['Content-Type' => 'application/json'],
        'body'    => json_encode($message),
        'method'  => 'POST'
    ]);
}


/**
 * Δημιουργία Weekly Snapshot + επιστροφή link
 */
function generate_weekly_snapshot_page() {

    $args = array(
        'post_type'      => array('post', 'page'),
        'posts_per_page' => -1,
        'date_query'     => array(
            array(
                'after' => '7 days ago'
            ),
        ),
        'orderby' => 'date',
        'order'   => 'DESC'
    );

    $query = new WP_Query($args);

    $content = '<div style="max-width:900px;margin:40px auto;font-family:Arial;background:#f7f7f7;padding:20px;border-radius:12px;">';

    $content .= '<h1 style="text-align:center;">📊 Εβδομαδιαία Σύνοψη</h1>';
    $content .= '<p style="text-align:center;color:#666;">
        Όλες τα νέα του site τις τελευταίες 7 ημέρες
    </p>';

    $content .= '<div style="display:grid;gap:18px;">';

    if ($query->have_posts()) {

        while ($query->have_posts()) {
            $query->the_post();

            $type = get_post_type() == 'post' ? '📝 Άρθρο' : '📄 Σελίδα';

            $image = get_the_post_thumbnail_url(get_the_ID(), 'medium');

            $excerpt = get_the_excerpt();
            if (!$excerpt) {
                $excerpt = wp_trim_words(strip_tags(get_the_content()), 25);
            }

            $content .= '
            <div style="background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.05);">

                ' . ($image ? '<img src="' . esc_url($image) . '" style="width:100%;height:180px;object-fit:cover;">' : '') . '

                <div style="padding:15px;">

                    <div style="font-size:12px;color:#888;">' . $type . '</div>

                    <a href="' . get_permalink() . '" style="
                        font-size:18px;
                        font-weight:700;
                        color:#111;
                        text-decoration:none;
                    ">
                        ' . get_the_title() . '
                    </a>

                    <p style="font-size:14px;color:#555;margin:10px 0;">
                        ' . esc_html($excerpt) . '
                    </p>

                    <div style="font-size:12px;color:#999;">
                        📅 ' . get_the_date() . '
                    </div>

                </div>
            </div>';
        }

    } else {
        $content .= '<p style="text-align:center;">Δεν υπάρχει νέο περιεχόμενο.</p>';
    }

    $content .= '</div></div>';

    wp_reset_postdata();

    // Update page
    $page = get_page_by_path('weekly-snapshot');

    if ($page) {
        wp_update_post([
            'ID'           => $page->ID,
            'post_content' => $content,
        ]);

        return get_permalink($page->ID);
    }

    return '';
}


/**
 * Cron κάθε 1 ώρα
 */
if (!wp_next_scheduled('weekly_snapshot_event')) {
    wp_schedule_event(time(), 'hourly', 'weekly_snapshot_event');
}
