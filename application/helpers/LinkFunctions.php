<?php
/**
 * @version $Id$
 * @copyright Center for History and New Media, 2007-2008
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka_ThemeHelpers
 * @subpackage LinkHelpers
 **/

/**
 * Uses uri() to generate <a> tags for a given link.
 * 
 * @since 0.10 No longer escapes the text for the link.  This text must be valid
 * HTML.
 * @since 0.10 No longer prepends the word 'View' to the text of the link.  Instead
 * 'View' is the default text.
 *
 * @param Omeka_Record|string $record The name of the controller to use for the
 * link.  If a record instance is passed, then it inflects the name of the 
 * controller from the record class.
 * @param string $action The action to use for the link (optional)
 * @param string $text The text to put in the link.  Default is 'View'.
 * @param array $props Attributes for the <a> tag
 * @return string HTML
 **/
function link_to($record, $action=null, $text='View', $props = array())
{
    // If we're linking directly to a record, use the URI for that record.
    if($record instanceof Omeka_Record) {
        $url = record_uri($record, $action);
    }
    else {
        // Otherwise $record is the name of the controller to link to.
        $urlOptions = array();
        //Use Zend Framework's built-in 'default' route
        $route = 'default';
        $urlOptions['controller'] = (string) $record;
        if($action) $urlOptions['action'] = (string) $action;
        $url = uri($urlOptions, $route);
    }

	$attr = !empty($props) ? ' ' . _tag_attributes($props) : '';
	return '<a href="'. $url . '"' . $attr . '>' . $text . '</a>';
}

/**
 * Retrieve HTML for a link to the advanced search form.
 * 
 * @since 0.10
 * @param string $text Optional Text of the link. Default is 'Advanced Search'.
 * @param array $props Optional XHTML attributes for the link.
 * @return string
 **/
function link_to_advanced_search($text = 'Advanced Search', $props = array())
{
    // Is appending the query string directly a security issue?  We should figure that out.
    $props['href'] = uri('items/advanced-search') . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
    return '<a ' . _tag_attributes($props) . '>' . $text . '</a>';
}

/**
 * Get the proper HTML for a link to the browse page for items, with any appropriate
 * filtering parameters passed to the URL.
 * 
 * @since 0.10
 * @param string $text Text to display in the link.
 * @param array $browseParams Optional Any parameters to use to build the browse page URL, e.g.
 * array('collection' => 1) would build items/browse?collection=1 as the URL.
 * @param array $linkProperties Optional XHTML attributes for the link.
 * @return string HTML
 **/
function link_to_browse_items($text, $browseParams = array(), $linkProperties = array())
{
    // Set the link href to the items/browse page.
    $linkProperties['href'] = uri(array('controller'=>'items', 'action'=>'browse'), 'default', $browseParams);
    return "<a " . _tag_attributes($linkProperties) . ">$text</a>";
}

/**
 * Link to the collection that the current item belongs to.
 * 
 * The default text displayed for this link will be the name of the collection,
 * but that can be changed by passing a string argument.
 * 
 * @since 0.10
 * @param string|null $text Optional Text for the link.
 * @param array $props Optional XHTML attributes for the <a> tag.
 * @param string $action Optional 'show' by default.
 * @return string
 **/
function link_to_collection_for_item($text = null, $props = array(), $action = 'show')
{
    return link_to_collection($text, $props, $action, get_collection_for_item());
}

/**
 * Retrieve the HTML for a link to the file metadata page for a particular file.
 * 
 * If no File object is specified, this will determine the file to use through
 * context.
 * 
 * The text of the link defaults to the original filename of the file unless
 * otherwise specified.
 * 
 * @since 1.0
 * @uses get_current_file()
 * @uses item_file()
 * @param array
 * @param string
 * @param File
 * @return string
 **/
function link_to_file_metadata($attributes = array(), $text = null, $file = null)
{
    if (!$file) {
        $file = get_current_file();
    }
    
    if (!$text) {
        // By default we should just display the original filename of the file.
        $text = item_file('Original Filename', null, array(), $file);
    }
    
    return link_to($file, 'show', $text, $attributes);
}

/**
 * @since 0.10 Function signature has changed so that the item to link to can be
 * determined by the context of the function call.  Also, text passed to the link
 * must be valid HTML (will not be automatically escaped because any HTML can be
 * passed in, e.g. an <img /> or the like).
 * 
 * @param string HTML for the text of the link.
 * @param array Properties for the <a> tag. (optional)
 * @param string The page to link to (this will be the 'show' page almost always
 * within the public theme).
 * @param Item Used for dependency injection testing or to use this function outside
 * the context of a loop.
 * @return string HTML
 **/
function link_to_item($text = null, $props = array(), $action = 'show', $item=null)
{
    if(!$item) {
        $item = get_current_item();
    }

	$text = (!empty($text) ? $text : strip_formatting(item('Dublin Core', 'Title')));
	
	return link_to($item, $action, $text, $props);
}

/**
 * @since 0.10 First argument is now the text of the link, 2nd argument are the 
 * query parameters to merge in to the href for the link.
 * 
 * @param string $text The text of the link.
 * @param array $params A set of query string parameters to merge in to the href
 * of the link.  E.g., if this link was clicked on the items/browse?collection=1
 * page, and array('foo'=>'bar') was passed as this argument, the new URI would be
 * items/browse?collection=1&foo=bar.
 */
function link_to_items_rss($text = 'RSS', $params=array())
{	
	return '<a href="' . items_output_uri('rss2', $params) . '" class="rss">' . $text . '</a>';
}

/**
 * Link to the item immediately following the current one.
 * 
 * @since 0.10 Signature has changed to reflect the use of get_current_item()
 * instead of passing the $item object as the first argument.
 * @uses get_current_item()
 * @uses link_to()
 * @return string
 **/
function link_to_next_item($text="Next Item --&gt;", $props=array())
{
    $item = get_current_item();
	if($next = $item->next()) {
		return link_to($next, 'show', $text, $props);
	}
}

/**
 * @see link_to_next_item()
 * @return string
 **/
function link_to_previous_item($text="&lt;-- Previous Item", $props=array())
{
    $item = get_current_item();
	if($previous = $item->previous()) {
		return link_to($previous, 'show', $text, $props);
	}
}

/**
 * 
 * @since 0.10 Signature has changed so that $text is the first argument.  Uses
 * get_current_collection() to determine what collection to link to.  Or you can 
 * pass it the Collection record as the last argument.
 * @param string $text Optional text to use for the title of the collection.  Default
 * behavior is to use the name of the collection.
 * @param array $props Set of attributes to use for the link.
 * @param array $action The action to link to for the collection.  Default is 'show'.
 * @param array $collectionObj Optional Collection record can be passed to this
 * to override the collection object retrieved by get_current_collection().
 * @return string
 **/
function link_to_collection($text=null, $props=array(), $action='show', $collectionObj = null)
{
    if (!$collectionObj) {
        $collectionObj = get_current_collection();
    }
    
	$text = (!empty($text) ? $text : (!empty($collectionObj->name) ? $collectionObj->name : '[Untitled]'));
	
	return link_to($collectionObj, $action, $text, $props);
}

/**
 * 
 * @since 0.10 All arguments to this function are optional.  If no text is given,
 * it will automatically use the text for the 'site_title' option.
 * @since 0.10 The text passed to this function will not be automatically escaped
 * with htmlentities(), which allows for passing images or other HTML in place of text.
 * @return string
 **/
function link_to_home_page($text = null, $props = array())
{
    if (!$text) {
        $text = settings('site_title');
    }
	$uri = WEB_ROOT;
	return '<a href="'.$uri.'" '._tag_attributes($props).'>' . $text . "</a>\n";
}

/**
 * 
 * @since 0.10 Arguments follow the same pattern as link_to_home_page().
 * @see link_to_home_page()
 * @return string
 **/
function link_to_admin_home_page($text = null, $props = array())
{
    if (!$text) {
        $text = settings('site_title');
    }
	return '<a href="'.admin_uri('').'" '._tag_attributes($props).'>'. $text."</a>\n";
}

/**
 * Retrieve a tag cloud of all the tags for the current item.
 * 
 * @since 0.10
 * @see item_tags_as_string()
 * @param string
 * @param boolean $tagsAreLinked Optional Whether or not to make each tag a link
 * to browse all the items with that tag.  True by default.
 * @param Item|null Check for this specific item record (current item if null).
 * @return string
 **/
function item_tags_as_cloud($order = null, $tagsAreLinked = true, $item=null)
{
    if (!$item) {
        $item = get_current_item();
    }
    $tags = get_tags(array('sort'=>$order, 'record'=>$item));
    $urlToLinkTo = ($tagsAreLinked) ? uri('items/browse/tag/') : null;
    return tag_cloud($tags, $urlToLinkTo);
}

/**
 * Output the tags for the current item as a string.
 * 
 * @todo Should this take a set of parameters instead of $order?  That would be 
 * good for limiting the # of tags returned by the query.
 * @since 0.10
 * @see item_tags_as_cloud()
 * @param string $delimiter String that separates each tag.  Default is a comma 
 * and space.
 * @param string|null $order Options include 'recent', 'most', 'least', 'alpha'.  
 * Default is null, which means that tags will display in the order they were
 * entered via the form.
 * @param boolean $tagsAreLinked If tags should be linked or just represented as
 * text.  Default is true.
 * @param Item|null Check for this specific item record (current item if null).
 * @return string HTML
 **/
function item_tags_as_string($delimiter = ', ', $order = null,  $tagsAreLinked = true, $item=null)
{
    if (!$item) {
        $item = get_current_item();
    }
    $tags = get_tags(array('sort'=>$order, 'record'=>$item));
    $urlToLinkTo = ($tagsAreLinked) ? uri('items/browse/tag/') : null;
    return tag_string($tags, $urlToLinkTo, $delimiter);
}

/**
 * Retrieve HTML for the set of pagination links.
 * 
 * @since 0.10
 * @param array $options Optional Configurable parameters for the pagination 
 * links.  The following options are available:
 *      'scrolling_style' (string) See Zend_View_Helper_PaginationControl
  * for more details.  Default 'Sliding'.  
 *      'partial_file' (string) View script to use to render the pagination HTML.
 * Default is 'common/pagination_control.php'.
 *      'page_range' (integer) See Zend_Paginator::setPageRange() for details.
 * Default is 5.
 *      'total_results' (integer) Total results to paginate through.  Default is
 * provided by the 'total_results' key of the 'pagination' array that is typically
 * registered by the controller.
 *      'page' (integer) Current page of the result set.  Default is the 'page'
 * key of the 'pagination' array.
 *      'per_page' (integer) Number of results to display per page.  Default is
 * the 'per_page' key of the 'pagination' array.
 * @return string HTML for the pagination links.
 **/
function pagination_links($options = array('scrolling_style' => null, 
                                     'partial_file'    => null, 
                                     'page_range'      => null, 
                                     'total_results'   => null, 
                                     'page'            => null, 
                                     'per_page'        => null))
{
    if (Zend_Registry::isRegistered('pagination')) {
        // If the pagination variables are registered, set them for local use.
        $p = Zend_Registry::get('pagination');
	} else {
        // If the pagination variables are not registered, set required defaults 
        // arbitrarily to avoid errors.
        $p = array('total_results'   => 1, 
                   'page'            => 1, 
                   'per_page'        => 1);
    }
    
    // Set preferred settings.
    $scrollingStyle   = $options['scrolling_style'] ? $options['scrolling_style']     : 'Sliding';
    $partial          = $options['partial_file']    ? $options['partial_file']        : 'common' . DIRECTORY_SEPARATOR . 'pagination_control.php';
    $pageRange        = $options['page_range']      ? (int) $options['page_range']    : 5;
    $totalCount       = $options['total_results']   ? (int) $options['total_results'] : (int) $p['total_results'];
    $pageNumber       = $options['page']            ? (int) $options['page']          : (int) $p['page'];
    $itemCountPerPage = $options['per_page']        ? (int) $options['per_page']      : (int) $p['per_page'];
    
    // Create an instance of Zend_Paginator.
    $paginator = Zend_Paginator::factory($totalCount);
    
    // Configure the instance.
    $paginator->setCurrentPageNumber($pageNumber)
              ->setItemCountPerPage($itemCountPerPage)
              ->setPageRange($pageRange);
    
    return __v()->paginationControl($paginator, 
                                    $scrollingStyle, 
                                    $partial);
}

/**
 * Helper function to be used in public themes to allow plugins to modify the navigation of those themes.
 *
 * Plugins can modify navigation by adding filters to specific subsets of the
 *  navigation. For instance, most themes will have what might be called a 'main'
 *  navigation set on the header of the site. This main navigation header would
 *  be attached to a filter called 'public_navigation_main', which would always
 *  act on that particular navigation. You would signal to the plugins to
 *  differentiate between the different navigation elements by passing the 2nd
 *  argument as 'main', so that it knew that this was the main navigation.
 *
 * @since 0.10
 * @see apply_filters()
 * @param array
 * @param string|null
 * @return string HTML
 **/
function public_nav(array $navArray, $navType=null)
{
    if ($navType) {
        $filterName = 'public_navigation_' . $navType;
        $navArray = apply_filters($filterName, $navArray);
    }
    return nav($navArray);
}

/**
 * Alias for public_nav($array, 'main'). This is to avoid potential typos so
 *  that all plugins can count on having at least a 'main' navigation filter in
 *  the public themes.
 * 
 * @since 0.10
 * @param array
 * @uses public_nav()
 * @return string
 **/
function public_nav_main(array $navArray)
{
    return public_nav($navArray, 'main');
}