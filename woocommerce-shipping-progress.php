<?php
/**
 * Plugin Name: WooCommerce Free Shipping Progress Bar
 * Plugin URI: https://github.com/uldtot
 * Description: En progress bar der viser hvor meget der mangler for at opnå gratis fragt i WooCommerce.
 * Version: 1.0.0
 * Author: Kim Vinberg
 * Author URI: https://dicm.dk
 * License: GPL-2.0+
 * Text Domain: woocommerce-free-shipping-progress
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class WC_Free_Shipping_Progress {
    public function __construct() {
        add_action('woocommerce_before_cart', [$this, 'add_free_shipping_progress_to_cart']);
        add_action('woocommerce_widget_shopping_cart_before_buttons', [$this, 'add_free_shipping_progress_to_cart']);
        add_shortcode('free_shipping_progress', [$this, 'free_shipping_progress_bar']);
    }

    public function get_free_shipping_minimum($zone_name = 'England') {
        if (!isset($zone_name)) return null;

        $result = null;
        $zone = null;

        $zones = WC_Shipping_Zones::get_zones();
        foreach ($zones as $z) {
            if ($z['zone_name'] == $zone_name) {
                $zone = $z;
            }
        }

        if ($zone) {
            $shipping_methods = $zone['shipping_methods'];
            foreach ($shipping_methods as $method) {
                if ($method->id == 'free_shipping') {
                    $result = $method->min_amount;
                    break;
                }
            }
        }

        return $result;
    }

    public function free_shipping_progress_bar() {
        $free_shipping_threshold = $this->get_free_shipping_minimum();
        if (!$free_shipping_threshold) {
            $free_shipping_threshold = 500;
        }
        $cart_total = WC()->cart->subtotal;
        $remaining = max(0, $free_shipping_threshold - $cart_total);
        $progress = min(100, ($cart_total / $free_shipping_threshold) * 100);

        ob_start();
        ?>
        <div class="shipping-progress-wrapper">
        <?php
                if($remaining == 0) {
?>
      <p>Du har opnået fri fragt!</p>
            <?php
                } else {
        ?>
        <p>Du er <?php echo wc_price($remaining); ?> fra at opnå fri fragt!</p>
                    <?php
                }
                    ?>
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: <?php echo $progress; ?>%;"></div>
            </div>
        </div>
        <style>
            .shipping-progress-wrapper {
                font-size: 16px;
                margin-bottom: 10px;
            }
            .progress-bar-container {
                width: 100%;
                height: 10px;
                background: #ddd;
                border-radius: 5px;
                overflow: hidden;
                margin-top: 5px;
            }
            .progress-bar {
                height: 100%;
                background: #28a745;
                transition: width 0.3s ease-in-out;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    public function add_free_shipping_progress_to_cart() {
        echo do_shortcode('[free_shipping_progress]');
    }
}

new WC_Free_Shipping_Progress();
