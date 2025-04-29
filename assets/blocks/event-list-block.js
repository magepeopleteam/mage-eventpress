( function( blocks, i18n, element, components, editor ) {
    var el = element.createElement;
    var __ = i18n.__;
    var InspectorControls = editor.InspectorControls;
    var PanelBody = components.PanelBody;
    var SelectControl = components.SelectControl;
    var TextControl = components.TextControl;
    var Placeholder = components.Placeholder;

    blocks.registerBlockType('mage/event-list', {
        title: __('WpEvently - By Magepeople', 'mage-eventpress'),
        icon: 'calendar',
        category: 'magepeople',
        description: __('Display a list of events with customizable settings.', 'mage-eventpress'),
        keywords: [ __('event', 'mage-eventpress'), __('list', 'mage-eventpress'), __('wpevently', 'mage-eventpress') ],
        
        attributes: {
            cat: {
                type: 'string',
                default: '0'
            },
            org: {
                type: 'string',
                default: '0'
            },
            style: {
                type: 'string',
                default: 'grid'
            },
            column: {
                type: 'number',
                default: 3
            },
            'cat-filter': {
                type: 'string',
                default: 'no'
            },
            'org-filter': {
                type: 'string',
                default: 'no'
            },
            show: {
                type: 'string',
                default: '-1'
            },
            pagination: {
                type: 'string',
                default: 'no'
            },
            city: {
                type: 'string',
                default: ''
            },
            country: {
                type: 'string',
                default: ''
            },
            'carousal-nav': {
                type: 'string',
                default: 'no'
            },
            'carousal-dots': {
                type: 'string',
                default: 'yes'
            },
            'carousal-id': {
                type: 'string',
                default: '102448'
            },
            'timeline-mode': {
                type: 'string',
                default: 'vertical'
            },
            sort: {
                type: 'string',
                default: 'ASC'
            },
            status: {
                type: 'string',
                default: 'upcoming'
            },
            'search-filter': {
                type: 'string',
                default: 'no'
            }
        },

        edit: function(props) {
            var attributes = props.attributes;

            function updateAttribute(attribute) {
                return function(value) {
                    var newAttributes = {};
                    newAttributes[attribute] = value;
                    props.setAttributes(newAttributes);
                };
            }

            return [
                el(InspectorControls, { key: 'inspector' },
                    el(PanelBody, { title: __('Basic Settings', 'mage-eventpress'), initialOpen: true },
                        el(TextControl, {
                            label: __('Category ID', 'mage-eventpress'),
                            help: __('Enter category ID (integer number only)', 'mage-eventpress'),
                            value: attributes.cat,
                            onChange: updateAttribute('cat')
                        }),
                        el(TextControl, {
                            label: __('Organizer ID', 'mage-eventpress'),
                            help: __('Enter organizer ID (integer number only)', 'mage-eventpress'),
                            value: attributes.org,
                            onChange: updateAttribute('org')
                        }),
                        el(SelectControl, {
                            label: __('Display Style', 'mage-eventpress'),
                            value: attributes.style,
                            options: [
                                { label: __('Grid', 'mage-eventpress'), value: 'grid' },
                                { label: __('List', 'mage-eventpress'), value: 'list' },
                                { label: __('Minimal', 'mage-eventpress'), value: 'minimal' },
                                { label: __('Native', 'mage-eventpress'), value: 'native' },
                                { label: __('Timeline', 'mage-eventpress'), value: 'timeline' },
                                { label: __('Title', 'mage-eventpress'), value: 'title' },
                                { label: __('Spring', 'mage-eventpress'), value: 'spring' },
                                { label: __('Winter', 'mage-eventpress'), value: 'winter' }
                            ],
                            onChange: updateAttribute('style')
                        }),
                        el(SelectControl, {
                            label: __('Columns', 'mage-eventpress'),
                            value: attributes.column,
                            options: [
                                { label: '3', value: 3 },
                                { label: '4', value: 4 }
                            ],
                            onChange: updateAttribute('column')
                        }),
                        el(TextControl, {
                            label: __('Number of Events', 'mage-eventpress'),
                            help: __('Enter -1 to show all events', 'mage-eventpress'),
                            value: attributes.show,
                            onChange: updateAttribute('show')
                        }),
                        el(SelectControl, {
                            label: __('Sort Order', 'mage-eventpress'),
                            value: attributes.sort,
                            options: [
                                { label: __('Ascending', 'mage-eventpress'), value: 'ASC' },
                                { label: __('Descending', 'mage-eventpress'), value: 'DESC' }
                            ],
                            onChange: updateAttribute('sort')
                        })
                    ),

                    el(PanelBody, { title: __('Filter Settings', 'mage-eventpress'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Category Filter', 'mage-eventpress'),
                            value: attributes['cat-filter'],
                            options: [
                                { label: __('No', 'mage-eventpress'), value: 'no' },
                                { label: __('Yes', 'mage-eventpress'), value: 'yes' }
                            ],
                            onChange: updateAttribute('cat-filter')
                        }),
                        el(SelectControl, {
                            label: __('Organizer Filter', 'mage-eventpress'),
                            value: attributes['org-filter'],
                            options: [
                                { label: __('No', 'mage-eventpress'), value: 'no' },
                                { label: __('Yes', 'mage-eventpress'), value: 'yes' }
                            ],
                            onChange: updateAttribute('org-filter')
                        }),
                        el(SelectControl, {
                            label: __('Search Filter', 'mage-eventpress'),
                            value: attributes['search-filter'],
                            options: [
                                { label: __('No', 'mage-eventpress'), value: 'no' },
                                { label: __('Yes', 'mage-eventpress'), value: 'yes' }
                            ],
                            onChange: updateAttribute('search-filter')
                        })
                    ),

                    el(PanelBody, { title: __('Pagination & Carousel', 'mage-eventpress'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Pagination', 'mage-eventpress'),
                            value: attributes.pagination,
                            options: [
                                { label: __('No', 'mage-eventpress'), value: 'no' },
                                { label: __('Yes', 'mage-eventpress'), value: 'yes' },
                                { label: __('Carousel', 'mage-eventpress'), value: 'carousal' }
                            ],
                            onChange: updateAttribute('pagination')
                        }),
                        attributes.pagination === 'carousal' && [
                            el(SelectControl, {
                                label: __('Carousel Navigation', 'mage-eventpress'),
                                value: attributes['carousal-nav'],
                                options: [
                                    { label: __('No', 'mage-eventpress'), value: 'no' },
                                    { label: __('Yes', 'mage-eventpress'), value: 'yes' }
                                ],
                                onChange: updateAttribute('carousal-nav')
                            }),
                            el(SelectControl, {
                                label: __('Carousel Dots', 'mage-eventpress'),
                                value: attributes['carousal-dots'],
                                options: [
                                    { label: __('No', 'mage-eventpress'), value: 'no' },
                                    { label: __('Yes', 'mage-eventpress'), value: 'yes' }
                                ],
                                onChange: updateAttribute('carousal-dots')
                            }),
                            el(TextControl, {
                                label: __('Carousel ID', 'mage-eventpress'),
                                help: __('Enter a unique ID (integer number only)', 'mage-eventpress'),
                                value: attributes['carousal-id'],
                                onChange: updateAttribute('carousal-id')
                            })
                        ]
                    ),

                    attributes.style === 'timeline' && el(PanelBody, { title: __('Timeline Settings', 'mage-eventpress'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Timeline Mode', 'mage-eventpress'),
                            value: attributes['timeline-mode'],
                            options: [
                                { label: __('Vertical', 'mage-eventpress'), value: 'vertical' },
                                { label: __('Horizontal', 'mage-eventpress'), value: 'horizontal' }
                            ],
                            onChange: updateAttribute('timeline-mode')
                        })
                    ),

                    el(PanelBody, { title: __('Location Filter', 'mage-eventpress'), initialOpen: false },
                        el(TextControl, {
                            label: __('City', 'mage-eventpress'),
                            help: __('Enter city name', 'mage-eventpress'),
                            value: attributes.city,
                            onChange: updateAttribute('city')
                        }),
                        el(TextControl, {
                            label: __('Country', 'mage-eventpress'),
                            help: __('Enter country name', 'mage-eventpress'),
                            value: attributes.country,
                            onChange: updateAttribute('country')
                        })
                    ),

                    el(PanelBody, { title: __('Event Status', 'mage-eventpress'), initialOpen: false },
                        el(SelectControl, {
                            label: __('Status', 'mage-eventpress'),
                            value: attributes.status,
                            options: [
                                { label: __('Upcoming', 'mage-eventpress'), value: 'upcoming' },
                                { label: __('Expired', 'mage-eventpress'), value: 'expired' }
                            ],
                            onChange: updateAttribute('status')
                        })
                    )
                ),
                
                el('div', { className: props.className },
                    el(Placeholder, {
                        icon: 'calendar-alt',
                        label: __('WpEvently - By Magepeople', 'mage-eventpress'),
                        instructions: __('Display a list of events with the selected settings.', 'mage-eventpress')
                    },
                        el('div', { className: 'mep-event-block-preview-settings' },
                            el('p', null, 
                                el('strong', null, __('Current Settings:', 'mage-eventpress'))
                            ),
                            el('ul', null,
                                el('li', null, __('Style: ', 'mage-eventpress') + attributes.style),
                                el('li', null, __('Columns: ', 'mage-eventpress') + attributes.column),
                                el('li', null, __('Show: ', 'mage-eventpress') + attributes.show),
                                el('li', null, __('Status: ', 'mage-eventpress') + attributes.status),
                                el('li', null, __('Pagination: ', 'mage-eventpress') + attributes.pagination),
                                attributes.pagination === 'carousal' && [
                                    el('li', null, __('Carousel Navigation: ', 'mage-eventpress') + attributes['carousal-nav']),
                                    el('li', null, __('Carousel Dots: ', 'mage-eventpress') + attributes['carousal-dots'])
                                ],
                                attributes.city && el('li', null, __('City: ', 'mage-eventpress') + attributes.city),
                                attributes.country && el('li', null, __('Country: ', 'mage-eventpress') + attributes.country)
                            )
                        )
                    )
                )
            ];
        },

        save: function() {
            return null; // Dynamic block, rendered in PHP
        }
    });
}(
    window.wp.blocks,
    window.wp.i18n,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor || window.wp.editor
) );
