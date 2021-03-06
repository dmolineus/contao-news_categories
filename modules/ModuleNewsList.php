<?php

/**
 * news_categories extension for Contao Open Source CMS
 *
 * Copyright (C) 2011-2014 Codefog
 *
 * @package news_categories
 * @link    http://codefog.pl
 * @author  Webcontext <http://webcontext.com>
 * @author  Codefog <info@codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */

namespace NewsCategories;

/**
 * Override the default front end module "news list".
 */
class ModuleNewsList extends \Contao\ModuleNewsList
{

    /**
     * Set the flag to filter news by categories
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            return parent::generate();
        }

        // Generate the list in related categories mode
        if ($this->news_relatedCategories) {
            return $this->generateRelated();
        }

        $GLOBALS['NEWS_FILTER_CATEGORIES'] = $this->news_filterCategories ? true : false;
        $GLOBALS['NEWS_FILTER_DEFAULT']    = deserialize($this->news_filterDefault, true);
        $GLOBALS['NEWS_FILTER_PRESERVE']   = $this->news_filterPreserve;

        $buffer = parent::generate();

        // Cleanup the $GLOBALS array (see #57)
        unset($GLOBALS['NEWS_FILTER_CATEGORIES'], $GLOBALS['NEWS_FILTER_DEFAULT'], $GLOBALS['NEWS_FILTER_PRESERVE']);

        return $buffer;
    }

    /**
     * Generate the list in related categories mode
     *
     * Use the categories of the current news item. The module must be
     * on the same page as news reader module.
     *
     * @return string
     */
    protected function generateRelated()
    {
        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && $GLOBALS['TL_CONFIG']['useAutoItem'] && isset($_GET['auto_item'])) {
            \Input::setGet('items', \Input::get('auto_item'));
        }

        // Return if there is no item specified
        if (!\Input::get('items')) {
            return '';
        }

        $this->news_archives = $this->sortOutProtected(deserialize($this->news_archives));

        // Return if there are no archives
        if (!is_array($this->news_archives) || empty($this->news_archives)) {
            return '';
        }

        $news = \NewsModel::findPublishedByParentAndIdOrAlias(\Input::get('items'), $this->news_archives);

        // Return if the news item was not found
        if ($news === null) {
            return '';
        }

        $GLOBALS['NEWS_FILTER_CATEGORIES'] = false;
        $GLOBALS['NEWS_FILTER_DEFAULT']    = deserialize($news->categories, true);
        $GLOBALS['NEWS_FILTER_EXCLUDE']    = array($news->id);

        return parent::generate();
    }
}
