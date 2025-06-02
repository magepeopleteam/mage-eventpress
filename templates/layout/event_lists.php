<?php ?>
<div class="wrap"></div>
<div class="mpwem_event_list mpStyle mpwem_welcome_page">
    <div class='padding'>
        <div class="container">
            <div class="header">
                <div class="header-top">
                    <h1>Event Management Dashboard</h1>
                    <button class="add-event-btn">
                        <span>+</span>
                        Add New Event
                    </button>
                </div>

                <div class="analytics">
                    <div class="analytics-card">
                        <h3>143</h3>
                        <p>Total Events</p>
                        <div class="trend up">↗ +12% this month</div>
                    </div>
                    <div class="analytics-card">
                        <h3>8</h3>
                        <p>Active Events</p>
                        <div class="trend neutral">→ Same as last week</div>
                    </div>
                    <div class="analytics-card">
                        <h3>2,847</h3>
                        <p>Total Registrations</p>
                        <div class="trend up">↗ +23% this month</div>
                    </div>
                    <div class="analytics-card">
                        <h3>94%</h3>
                        <p>Avg. Capacity Filled</p>
                        <div class="trend up">↗ +5% this month</div>
                    </div>
                    <div class="analytics-card">
                        <h3>$12,450</h3>
                        <p>Revenue This Month</p>
                        <div class="trend up">↗ +18% vs last month</div>
                    </div>
                </div>

                <div class="stats-summary">
                    <div class="stat-item">
                        <span>All Events</span>
                        <span class="stat-number">43</span>
                    </div>
                    <div class="stat-item">
                        <span>My Events</span>
                        <span class="stat-number">42</span>
                    </div>
                    <div class="stat-item">
                        <span>Published</span>
                        <span class="stat-number">42</span>
                    </div>
                    <div class="stat-item">
                        <span>Draft</span>
                        <span class="stat-number">1</span>
                    </div>
                </div>
            </div>

            <div class="controls">
                <div class="search-box">
                    <div class="search-icon">🔍</div>
                    <input type="text" placeholder="Search events, locations, or organizers...">
                </div>
                <select class="category-select">
                    <option>All Categories</option>
                    <option>Education</option>
                    <option>Travel</option>
                    <option>Entertainment</option>
                    <option>Reunion Event</option>
                    <option>Indoor Games</option>
                    <option>Cooking Class</option>
                </select>
                <button class="filter-btn">Filter</button>
                <button class="filter-btn">Export</button>
            </div>

            <div class="table-container">
                <table class="event-table">
                    <thead>
                    <tr>
                        <th>Image</th>
                        <th>Event Name</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Event Date</th>
                        <th>Ticket Types</th>
                        <th>Capacity</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <div class="event-image-placeholder">📚</div>
                        </td>
                        <td>
                            <div class="event-name">
                                Math Learning — Elementor
                                <div class="event-status-inline">
                                    <div class="status-live-inline">
                                        <div class="live-indicator-inline"></div>
                                        Live
                                    </div>
                                </div>
                            </div>
                            <div class="event-category">Education</div>
                        </td>
                        <td>
                            <div class="status-badge status-expired">Expired</div>
                        </td>
                        <td>
                            <div class="location">
                                📍 Online Platform
                            </div>
                        </td>
                        <td>
                            <div class="date-time">
                                Sep 15, 2024
                                <span class="time">10:00 PM</span>
                            </div>
                        </td>
                        <td>
                            <div class="ticket-types">
                                <div class="ticket-item">
                                    <span class="ticket-name">Standard</span>
                                    <span class="ticket-price ticket-free">Free</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Premium</span>
                                    <span class="ticket-price">$29</span>
                                </div>
                                <div class="ticket-more">+1 more</div>
                            </div>
                        </td>
                        <td class="capacity">
                            <div class="capacity-number">100/100</div>
                            <div class="capacity-bar">
                                <div class="capacity-fill capacity-full" style="width: 100%"></div>
                            </div>
                            <div class="capacity-status">Full</div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn view" title="View Event">👁️</button>
                                <button class="action-btn edit" title="Edit Event">✏️</button>
                                <button class="action-btn delete" title="Delete Event">🗑️</button>
                                <button class="action-btn duplicate" title="Duplicate Event">📋</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="event-image-placeholder">🏖️</div>
                        </td>
                        <td>
                            <div class="event-name">
                                Coxesbazar Sea Beach Chair Booking
                                <div class="event-status-inline">
                                    <div class="status-live-inline">
                                        <div class="live-indicator-inline"></div>
                                        Live
                                    </div>
                                </div>
                            </div>
                            <div class="event-category">Travel</div>
                        </td>
                        <td>
                            <div class="status-badge status-active">Active</div>
                        </td>
                        <td>
                            <div class="location">
                                📍 Cox's Bazar, Bangladesh
                            </div>
                        </td>
                        <td>
                            <div class="date-time">
                                Feb 4, 2026
                                <span class="time">5:00 AM</span>
                            </div>
                        </td>
                        <td>
                            <div class="ticket-types">
                                <div class="ticket-item">
                                    <span class="ticket-name">Beach Chair</span>
                                    <span class="ticket-price">$15</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">VIP Cabana</span>
                                    <span class="ticket-price">$45</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Group Package</span>
                                    <span class="ticket-price">$120</span>
                                </div>
                                <div class="ticket-more">+2 more</div>
                            </div>
                        </td>
                        <td class="capacity">
                            <div class="capacity-number">0/40</div>
                            <div class="capacity-bar">
                                <div class="capacity-fill capacity-low" style="width: 0%"></div>
                            </div>
                            <div class="capacity-status">Available</div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn view" title="View Event">👁️</button>
                                <button class="action-btn edit" title="Edit Event">✏️</button>
                                <button class="action-btn delete" title="Delete Event">🗑️</button>
                                <button class="action-btn duplicate" title="Duplicate Event">📋</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="event-image-placeholder">🎬</div>
                        </td>
                        <td>
                            <div class="event-name">
                                Cinebarre Palace Station Las Vegas
                                <div class="event-status-inline">
                                    <div class="status-draft-inline">Draft</div>
                                </div>
                            </div>
                            <div class="event-category">Entertainment</div>
                        </td>
                        <td>
                            <div class="status-badge status-upcoming">Upcoming</div>
                        </td>
                        <td>
                            <div class="location">
                                📍 Las Vegas, Nevada
                            </div>
                        </td>
                        <td>
                            <div class="date-time">
                                May 27, 2025
                                <span class="time">10:50 AM</span>
                            </div>
                        </td>
                        <td>
                            <div class="ticket-types">
                                <div class="ticket-item">
                                    <span class="ticket-name">General</span>
                                    <span class="ticket-price">$25</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Premium</span>
                                    <span class="ticket-price">$40</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">VIP</span>
                                    <span class="ticket-price">$65</span>
                                </div>
                                <div class="ticket-more">+1 more</div>
                            </div>
                        </td>
                        <td class="capacity">
                            <div class="capacity-number">15/60</div>
                            <div class="capacity-bar">
                                <div class="capacity-fill capacity-low" style="width: 25%"></div>
                            </div>
                            <div class="capacity-status">Available</div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn view" title="View Event">👁️</button>
                                <button class="action-btn edit" title="Edit Event">✏️</button>
                                <button class="action-btn delete" title="Delete Event">🗑️</button>
                                <button class="action-btn duplicate" title="Duplicate Event">📋</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="event-image-placeholder">🎓</div>
                        </td>
                        <td>
                            <div class="event-name">
                                Springfield College Reunion
                                <div class="event-status-inline">
                                    <div class="status-live-inline">
                                        <div class="live-indicator-inline"></div>
                                        Live
                                    </div>
                                </div>
                            </div>
                            <div class="event-category">Reunion Event</div>
                        </td>
                        <td>
                            <div class="status-badge status-expired">Expired</div>
                        </td>
                        <td>
                            <div class="location">
                                📍 Springfield Campus
                            </div>
                        </td>
                        <td>
                            <div class="date-time">
                                Jan 1, 1970
                                <span class="time">5:00 AM</span>
                            </div>
                        </td>
                        <td>
                            <div class="ticket-types">
                                <div class="ticket-item">
                                    <span class="ticket-name">Alumni</span>
                                    <span class="ticket-price ticket-free">Free</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Guest</span>
                                    <span class="ticket-price">$20</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Dinner</span>
                                    <span class="ticket-price">$35</span>
                                </div>
                            </div>
                        </td>
                        <td class="capacity">
                            <div class="capacity-number">100/100</div>
                            <div class="capacity-bar">
                                <div class="capacity-fill capacity-full" style="width: 100%"></div>
                            </div>
                            <div class="capacity-status">Full</div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn view" title="View Event">👁️</button>
                                <button class="action-btn edit" title="Edit Event">✏️</button>
                                <button class="action-btn delete" title="Delete Event">🗑️</button>
                                <button class="action-btn duplicate" title="Duplicate Event">📋</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="event-image-placeholder">🎯</div>
                        </td>
                        <td>
                            <div class="event-name">
                                Carrom Board Game Tournament
                                <div class="event-status-inline">
                                    <div class="status-live-inline">
                                        <div class="live-indicator-inline"></div>
                                        Live
                                    </div>
                                </div>
                            </div>
                            <div class="event-category">Indoor Games</div>
                        </td>
                        <td>
                            <div class="status-badge status-active">Active</div>
                        </td>
                        <td>
                            <div class="location">
                                📍 Sports Complex
                            </div>
                        </td>
                        <td>
                            <div class="date-time">
                                Jun 4, 2025
                                <span class="time">10:00 AM</span>
                            </div>
                        </td>
                        <td>
                            <div class="ticket-types">
                                <div class="ticket-item">
                                    <span class="ticket-name">Single</span>
                                    <span class="ticket-price">$10</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Team (4)</span>
                                    <span class="ticket-price">$35</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">VIP Pass</span>
                                    <span class="ticket-price">$50</span>
                                </div>
                                <div class="ticket-more">+2 more</div>
                            </div>
                        </td>
                        <td class="capacity">
                            <div class="capacity-number">150/200</div>
                            <div class="capacity-bar">
                                <div class="capacity-fill capacity-high" style="width: 75%"></div>
                            </div>
                            <div class="capacity-status">75% Full</div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn view" title="View Event">👁️</button>
                                <button class="action-btn edit" title="Edit Event">✏️</button>
                                <button class="action-btn delete" title="Delete Event">🗑️</button>
                                <button class="action-btn duplicate" title="Duplicate Event">📋</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="event-image-placeholder">🏓</div>
                        </td>
                        <td>
                            <div class="event-name">
                                Table Tennis Championship
                                <div class="event-status-inline">
                                    <div class="status-draft-inline">Draft</div>
                                </div>
                            </div>
                            <div class="event-category">Indoor Games</div>
                        </td>
                        <td>
                            <div class="status-badge status-expired">Expired</div>
                        </td>
                        <td>
                            <div class="location">
                                📍 Recreation Center
                            </div>
                        </td>
                        <td>
                            <div class="date-time">
                                Jan 1, 2025
                                <span class="time">10:00 AM</span>
                            </div>
                        </td>
                        <td>
                            <div class="ticket-types">
                                <div class="ticket-item">
                                    <span class="ticket-name">Player</span>
                                    <span class="ticket-price">$15</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Spectator</span>
                                    <span class="ticket-price ticket-free">Free</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Pro League</span>
                                    <span class="ticket-price">$30</span>
                                </div>
                            </div>
                        </td>
                        <td class="capacity">
                            <div class="capacity-number">100/100</div>
                            <div class="capacity-bar">
                                <div class="capacity-fill capacity-full" style="width: 100%"></div>
                            </div>
                            <div class="capacity-status">Full</div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn view" title="View Event">👁️</button>
                                <button class="action-btn edit" title="Edit Event">✏️</button>
                                <button class="action-btn delete" title="Delete Event">🗑️</button>
                                <button class="action-btn duplicate" title="Duplicate Event">📋</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="event-image-placeholder">🍳</div>
                        </td>
                        <td>
                            <div class="event-name">
                                Traditional French Cuisine Masterclass
                                <div class="event-status-inline">
                                    <div class="status-live-inline">
                                        <div class="live-indicator-inline"></div>
                                        Live
                                    </div>
                                </div>
                            </div>
                            <div class="event-category">Cooking Class</div>
                        </td>
                        <td>
                            <div class="status-badge status-expired">Expired</div>
                        </td>
                        <td>
                            <div class="location">
                                📍 Culinary Studio
                            </div>
                        </td>
                        <td>
                            <div class="date-time">
                                Jan 3, 2025
                                <span class="time">11:00 AM</span>
                            </div>
                        </td>
                        <td>
                            <div class="ticket-types">
                                <div class="ticket-item">
                                    <span class="ticket-name">Basic</span>
                                    <span class="ticket-price">$75</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Premium</span>
                                    <span class="ticket-price">$120</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Master Chef</span>
                                    <span class="ticket-price">$200</span>
                                </div>
                                <div class="ticket-more">+1 more</div>
                            </div>
                        </td>
                        <td class="capacity">
                            <div class="capacity-number">100/100</div>
                            <div class="capacity-bar">
                                <div class="capacity-fill capacity-full" style="width: 100%"></div>
                            </div>
                            <div class="capacity-status">Full</div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn view" title="View Event">👁️</button>
                                <button class="action-btn edit" title="Edit Event">✏️</button>
                                <button class="action-btn delete" title="Delete Event">🗑️</button>
                                <button class="action-btn duplicate" title="Duplicate Event">📋</button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="event-image-placeholder">🍫</div>
                        </td>
                        <td>
                            <div class="event-name">
                                Classic Chocolate Desserts Workshop
                                <div class="event-status-inline">
                                    <div class="status-draft-inline">Draft</div>
                                </div>
                            </div>
                            <div class="event-category">Cooking Class</div>
                        </td>
                        <td>
                            <div class="status-badge status-expired">Expired</div>
                        </td>
                        <td>
                            <div class="location">
                                📍 Baking Studio
                            </div>
                        </td>
                        <td>
                            <div class="date-time">
                                Jan 3, 2025
                                <span class="time">10:00 AM</span>
                            </div>
                        </td>
                        <td>
                            <div class="ticket-types">
                                <div class="ticket-item">
                                    <span class="ticket-name">Workshop</span>
                                    <span class="ticket-price">$55</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Take-home Kit</span>
                                    <span class="ticket-price">$85</span>
                                </div>
                                <div class="ticket-item">
                                    <span class="ticket-name">Couple Pack</span>
                                    <span class="ticket-price">$140</span>
                                </div>
                                <div class="ticket-more">+2 more</div>
                            </div>
                        </td>
                        <td class="capacity">
                            <div class="capacity-number">100/100</div>
                            <div class="capacity-bar">
                                <div class="capacity-fill capacity-full" style="width: 100%"></div>
                            </div>
                            <div class="capacity-status">Full</div>
                        </td>
                        <td>
                            <div class="actions">
                                <button class="action-btn view" title="View Event">👁️</button>
                                <button class="action-btn edit" title="Edit Event">✏️</button>
                                <button class="action-btn delete" title="Delete Event">🗑️</button>
                                <button class="action-btn duplicate" title="Duplicate Event">📋</button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <div class="pagination">
                <div class="pagination-info">
                    Showing 8 of 43 events
                </div>
                <button class="load-more-btn" id="loadMoreBtn">
                    <span>Load More Events</span>
                    <span>↓</span>
                </button>
            </div>
        </div>
    </div>
</div>
