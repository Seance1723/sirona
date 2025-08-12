<?php
/**
 * Mega menu walker.
 */
class FortiveaX_Mega_Menu_Walker extends Walker_Nav_Menu {
    private $mega_enabled = false;
    private $mega_cols = 0;
    private $mega_styles = '';
    private $mega_custom = '';

    public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        $is_mega = $depth === 0 && get_post_meta( $item->ID, '_fx_mega_enabled', true );
        if ( $is_mega ) {
            $this->mega_enabled = true;
            $this->mega_cols    = (int) get_post_meta( $item->ID, '_fx_mega_cols', true );
            $bg_color           = get_post_meta( $item->ID, '_fx_mega_bg_color', true );
            $bg_image           = get_post_meta( $item->ID, '_fx_mega_bg_image', true );
            $width              = get_post_meta( $item->ID, '_fx_mega_width', true );
            $this->mega_custom  = get_post_meta( $item->ID, '_fx_mega_custom', true );
            $style = '';
            if ( $bg_color ) {
                $style .= 'background-color:' . esc_attr( $bg_color ) . ';';
            }
            if ( $bg_image ) {
                $style .= 'background-image:url(' . esc_url( $bg_image ) . ');';
            }
            if ( $width ) {
                $style .= 'width:' . esc_attr( $width ) . ';';
            }
            $this->mega_styles = $style;
            $item->classes[]   = 'menu-item-has-mega';
        } else {
            if ( $depth === 0 ) {
                $this->mega_enabled = false;
            }
        }
        $classes     = implode( ' ', array_map( 'sanitize_html_class', $item->classes ) );
        $output     .= '<li class="' . $classes . '">';
        $atts        = empty( $item->url ) ? '' : ' href="' . esc_url( $item->url ) . '"';
        $output     .= '<a' . $atts . '>' . esc_html( $item->title ) . '</a>';
    }

    public function end_el( &$output, $item, $depth = 0, $args = array() ) {
        $output .= '</li>';
    }

    public function start_lvl( &$output, $depth = 0, $args = array() ) {
        if ( $depth === 0 && $this->mega_enabled ) {
            $cols = max( 2, min( 6, $this->mega_cols ? $this->mega_cols : 3 ) );
            $style = $this->mega_styles ? ' style="' . esc_attr( $this->mega_styles ) . '"' : '';
            $output .= '<div class="fx-mega fx-cols-' . $cols . '"' . $style . '><ul class="sub-menu">';
        } else {
            $output .= '<ul class="sub-menu">';
        }
    }

    public function end_lvl( &$output, $depth = 0, $args = array() ) {
        if ( $depth === 0 && $this->mega_enabled ) {
            $output .= '</ul>';
            if ( $this->mega_custom ) {
                $output .= '<div class="fx-col fx-custom">' . do_shortcode( $this->mega_custom ) . '</div>';
            }
            $output .= '</div>';
        } else {
            $output .= '</ul>';
        }
    }

    public function display_element( $element, &$children_elements, $max_depth, $depth = 0, $args, &$output ) {
        if ( $this->mega_enabled && $depth === 1 ) {
            $element->classes[] = 'fx-col';
        }
        parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
    }
}