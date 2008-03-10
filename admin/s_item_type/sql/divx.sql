#########################################################
# OpenDb 1.5.0a7 'DIVX' Item Type
#########################################################

#
# Item Type
#
INSERT INTO s_item_type ( s_item_type, description, image ) VALUES ( 'DIVX', 'DivX Video', 'divX.gif' );

#
# Item Type Group Relationships
#
INSERT INTO s_item_type_group_rltshp ( s_item_type_group, s_item_type ) VALUES ( 'VIDEO', 'DIVX' );

#
# Attributes (non-core)
#
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'ALT_TITLE', 'Alternate Title', 'Alternate Title', 'text', '50', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DISC_ID', 'Disc ID', 'Disc ID', 'text', '10', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'IMAGEURL', 'Item Image URL', 'Image', 'url', '50', '*', 'IMAGE', NULL, NULL, 'hidden',NULL, NULL, NULL, NULL, NULL, 'N', 'Y', 'N', 'N', 'IMAGE', NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'ALT_ID', 'Alternate Item ID', 'Alt Item ID', 'text', '10', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'IMDBRATING', 'IMDB User Rating', 'IMDB Rating', 'hidden', NULL, NULL, NULL, NULL, NULL, 'star_rating','10', '%starrating% %value%/%maxrange%', NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'MOVIE_PLOT', 'Plot of a Movie', 'Plot', 'textarea', '50', '5', NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DIRECTOR', 'Director of a Movie', 'Director(s)', 'text', '50', NULL, NULL, NULL, NULL, 'list','names', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'ACTORS', 'List of Actors in a movie', 'Actor(s)', 'text', '50', NULL, NULL, NULL, NULL, 'list','names', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'Y', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'MOVIEGENRE', 'Movie Genre', 'Genre', 'checkbox_grid', '%display%', 'VERTICAL', NULL, NULL, NULL, 'category','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', 'CATEGORY', NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'YEAR', 'Year of Release', 'Year', 'number', '4', NULL, NULL, NULL, NULL, 'display','%value%', NULL, NULL, NULL, NULL, 'Y', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'RUN_TIME', 'Running time', 'Length (minutes)', 'number', '4', NULL, NULL, NULL, NULL, 'format_mins','%h %H %m %M', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'AUDIO_LANG', 'Audio Languages', 'Language(s)', 'checkbox_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'SUBTITLES', 'Subtitle languages', 'Subtitles', 'checkbox_grid', '%img% %display%', 'VERTICAL', NULL, NULL, NULL, 'display','%img% %display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'AGE_RATING', 'Age Recommendation', 'Age', 'radio_grid', '%img% %display%', NULL, NULL, NULL, NULL, 'display','%img%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'RATIO', 'Aspect Ratio of Movie', 'Aspect Ratio(s)', 'checkbox_grid', '%value%', 'VERTICAL', NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DIVXVIDVER', 'Video Codec Version', 'Video Codec', 'single_select', '%display%', NULL, NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DIVXAUDVER', 'Audio Codec Version', 'Audio Codec', 'single_select', '%display%', NULL, NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DIVXQUALIT', 'DIVX Quality', 'Quality', 'single_select', '%display%', NULL, NULL, NULL, NULL, 'display','%display%', NULL, NULL, NULL, NULL, 'Y', 'N', 'Y', 'N', NULL, NULL);
INSERT INTO s_attribute_type ( s_attribute_type, description, prompt, input_type, input_type_arg1, input_type_arg2, input_type_arg3, input_type_arg4, input_type_arg5, display_type, display_type_arg1, display_type_arg2, display_type_arg3, display_type_arg4, display_type_arg5, listing_link_ind, file_attribute_ind, lookup_attribute_ind, multi_attribute_ind, s_field_type, site_type ) VALUES ( 'DVD_EXTRAS', 'DVD Extra Features Details', 'Extras', 'textarea', '50', '5', NULL, NULL, NULL, 'list','ticks', NULL, NULL, NULL, NULL, 'N', 'N', 'N', 'N', NULL, NULL);

#
# Item Attribute Relationships
#
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'S_ITEM_ID', 0, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'S_TITLE', 1, NULL, 'N', 'Y', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'ALT_TITLE', 1, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'DISC_ID', 1, NULL, 'Y', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'IMAGEURL', 2, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'ALT_ID', 2, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'IMDBRATING', 5, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'MOVIE_PLOT', 20, NULL, 'N', 'N', 'Y', 'Y' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'DIRECTOR', 30, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'ACTORS', 40, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'MOVIEGENRE', 50, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'YEAR', 60, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'RUN_TIME', 70, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'AUDIO_LANG', 80, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'SUBTITLES', 90, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'AGE_RATING', 100, NULL, 'N', 'N', 'Y', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'RATIO', 110, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'DIVXVIDVER', 120, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'DIVXAUDVER', 130, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'DIVXQUALIT', 140, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'DVD_EXTRAS', 160, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'S_STATUS', 253, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'S_STATCMNT', 254, NULL, 'N', 'N', 'N', 'N' );
INSERT INTO s_item_attribute_type ( s_item_type, s_attribute_type, order_no, prompt, instance_attribute_ind, compulsory_ind, printable_ind, rss_ind ) VALUES ( 'DIVX', 'S_DURATION', 255, NULL, 'N', 'N', 'Y', 'N' );

#
# Attribute Type Lookup (MOVIEGENRE)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Action', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Adult', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Adventure', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Animation', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Audio', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Biblical', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Comedy', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Documentary', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Drama', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Fantasy', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Horror', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Music', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Musical', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Mystery', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Other', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Romance', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'ScienceFiction', 'Science Fiction', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Suspense', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Thriller', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'War', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'MOVIEGENRE', NULL, 'Western', '', '', 'N' );

#
# Attribute Type Lookup (AUDIO_LANG)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ENGLISH', 'English', 'usa.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'FINNISH', 'Finnish', 'finnish.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'FRENCH', 'French', 'french.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'GERMAN', 'German', 'german.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'ITALIAN', 'Italian', 'italian.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AUDIO_LANG', NULL, 'SPANISH', 'Spanish', 'spanish.gif', 'N' );

#
# Attribute Type Lookup (SUBTITLES)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', 0, 'COMMENTARY', 'Commentary', 'director.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', 0, 'TRIVIA', 'Trivia', 'director.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', 1, 'ENGLISH', 'English', 'usa.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', 1, 'ENG_H_IMP', 'English (Hearing Impaired)', 'english.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', 1, 'FRENCH', 'French', 'french.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', 1, 'GERMAN', 'German', 'german.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', 1, 'ITALIAN', 'Italian', 'italian.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'SUBTITLES', 1, 'SPANISH', 'Spanish', 'spanish.gif', 'N' );

#
# Attribute Type Lookup (AGE_RATING)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AGE_RATING', 0, 'G', 'General Exhibition', 'G.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AGE_RATING', 1, 'PG', 'Parental Guidance Recommended', 'PG.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AGE_RATING', 2, 'M', 'Mature Audience Recommended', 'M.gif', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AGE_RATING', 3, 'MA', 'Mature Audience', 'MA.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AGE_RATING', 4, 'R', 'Restricted 18+', 'R.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AGE_RATING', 5, 'X', 'Adult Videos', 'X.gif', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'AGE_RATING', 6, 'NR', 'Not Rated Material', 'NR.gif', 'N' );

#
# Attribute Type Lookup (RATIO)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'RATIO', NULL, '1.33', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'RATIO', NULL, '1.78', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'RATIO', NULL, '1.85', '', '', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'RATIO', NULL, '2.20', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'RATIO', NULL, '2.35', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'RATIO', NULL, '2.40', '', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'RATIO', NULL, '2.78', '', '', 'N' );

#
# Attribute Type Lookup (DIVXVIDVER)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXVIDVER', NULL, 'angelpotion', 'Angel Potion Codec', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXVIDVER', NULL, 'divx3', 'Divx3 3.11 Alpha', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXVIDVER', NULL, 'divx4', 'Divx4', '', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXVIDVER', NULL, 'divx5', 'Divx5', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXVIDVER', NULL, 'xvid', 'Xvid', '', 'N' );

#
# Attribute Type Lookup (DIVXAUDVER)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXAUDVER', NULL, 'mp2', 'Mp2', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXAUDVER', NULL, 'mp3', 'Mp3', '', 'Y' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXAUDVER', NULL, 'ogg', 'Ogg', '', 'N' );

#
# Attribute Type Lookup (DIVXQUALIT)
#
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXQUALIT', NULL, 'Good', 'Good', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXQUALIT', NULL, 'medium', 'Medium', '', 'N' );
INSERT INTO s_attribute_type_lookup ( s_attribute_type, order_no, value, display, img, checked_ind ) VALUES ( 'DIVXQUALIT', NULL, 'poor', 'Poor', '', 'N' );
