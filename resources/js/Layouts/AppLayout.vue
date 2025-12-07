<template>
    <div class="vscode-layout h-screen flex flex-col overflow-hidden bg-[#1e1e1e]">
        <!-- Top Navigation Bar (LinkedIn Style + Browser Search) -->
        <div class="top-nav-bar bg-[#283e4a] text-[#e7e9ec] h-14 flex items-center px-4 border-b border-[#1c2a33] flex-shrink-0 shadow-sm">
            <!-- Logo -->
            <Link href="/" class="flex items-center mr-4 flex-shrink-0">
                <div class="w-9 h-9 bg-[#0077b5] rounded flex items-center justify-center">
                    <span class="text-white font-bold text-lg">PR</span>
                </div>
            </Link>

            <!-- Browser-style Search Bar with Navigation -->
            <div class="flex-1 max-w-2xl mr-4 flex items-center gap-2">
                <!-- Navigation Arrows -->
                <div class="flex items-center gap-1">
                    <button
                        @click="goBack"
                        :disabled="!canGoBack"
                        class="p-1.5 rounded hover:bg-[#1c2a33] disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                        title="Back"
                    >
                        <svg class="w-4 h-4 text-[#e7e9ec]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button
                        @click="goForward"
                        :disabled="!canGoForward"
                        class="p-1.5 rounded hover:bg-[#1c2a33] disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
                        title="Forward"
                    >
                        <svg class="w-4 h-4 text-[#e7e9ec]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <!-- Search/Address Bar -->
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-4 h-4 text-[#a0a0a0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search posts, users, code..."
                        class="w-full pl-10 pr-4 py-2 bg-[#1c2a33] border border-[#1c2a33] rounded-lg text-[#e7e9ec] placeholder-[#a0a0a0] focus:outline-none focus:ring-2 focus:ring-[#0077b5] focus:border-[#0077b5] transition-all"
                        @keydown.enter="handleSearch"
                    />
                </div>

                <!-- Chat/Messages Icon -->
                <Link
                    href="/messages"
                    class="p-2 rounded hover:bg-[#1c2a33] transition-colors relative"
                    title="Messages"
                >
                    <svg class="w-5 h-5 text-[#e7e9ec]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    <!-- Notification stars overlay -->
                    <div class="absolute -top-1 -right-1 flex gap-0.5">
                        <svg class="w-2 h-2 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                        <svg class="w-2 h-2 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    </div>
                </Link>

                <!-- Dropdown Chevron -->
                <button
                    @click.prevent="showSearchMenu = !showSearchMenu"
                    class="p-1.5 rounded hover:bg-[#1c2a33] transition-colors"
                    title="More options"
                >
                    <svg class="w-4 h-4 text-[#e7e9ec]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
            </div>

            <!-- Navigation Icons -->
            <div class="flex items-center space-x-1 flex-1 justify-center">
                <!-- Home -->
                <Link
                    href="/"
                    :class="['nav-item', isActive('/') && !isActive('/feed') ? 'active' : '']"
                    title="Home"
                >
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        <span class="nav-label">Home</span>
                    </div>
                </Link>

                <!-- My Network -->
                <Link
                    href="/network"
                    :class="['nav-item', isActive('/network') ? 'active' : '']"
                    title="My Network"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <span class="nav-label">My Network</span>
                </Link>

                <!-- Jobs -->
                <Link
                    href="/jobs"
                    :class="['nav-item', isActive('/jobs') ? 'active' : '']"
                    title="Jobs"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="nav-label">Jobs</span>
                </Link>

                <!-- Messaging -->
                <Link
                    href="/messages"
                    :class="['nav-item', isActive('/messages') ? 'active' : '']"
                    title="Messaging"
                >
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <span class="nav-label">Messaging</span>
                    </div>
                </Link>

                <!-- Notifications -->
                <Link
                    href="/notifications"
                    :class="['nav-item', isActive('/notifications') ? 'active' : '']"
                    title="Notifications"
                >
                    <div class="relative">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <span class="nav-label">Notifications</span>
                    </div>
                </Link>

                <!-- Me (Profile) -->
                <div
                    v-if="$page.props.auth.user"
                    class="nav-item relative"
                    @click.prevent="showAccountMenu = !showAccountMenu"
                >
                    <div class="flex items-center gap-1">
                        <div class="w-6 h-6 rounded-full bg-[#0077b5] flex items-center justify-center text-white text-xs font-semibold">
                            {{ $page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                        </div>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <span class="nav-label">Me</span>
                </div>

                <!-- For Business -->
                <div class="nav-item relative">
                    <div class="flex items-center gap-1">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <span class="nav-label">For Business</span>
                </div>

                <!-- Learning -->
                <Link
                    href="/learning"
                    :class="['nav-item', isActive('/learning') ? 'active' : '']"
                    title="Learning"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                    <span class="nav-label">Learning</span>
                </Link>

                <!-- Theme Toggle -->
                <button
                    @click="toggleTheme"
                    class="nav-item"
                    title="Toggle theme"
                >
                    <svg v-if="isDarkMode" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                    <span class="nav-label">{{ isDarkMode ? 'Light' : 'Dark' }}</span>
                </button>
            </div>
        </div>

        <!-- Main Container -->
        <div class="flex flex-1 overflow-hidden">
            <!-- Activity Bar (Leftmost) -->
            <div class="activity-bar bg-[#2d2d30] w-14 flex flex-col items-center py-2 border-r border-[#3e3e42] flex-shrink-0">
                <Link 
                    href="/" 
                    :class="['activity-icon', isActive('/') ? 'active' : '']"
                    title="Explorer"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                    </svg>
                </Link>
                <Link 
                    href="/feed" 
                    :class="['activity-icon', isActive('/feed') ? 'active' : '']"
                    title="Feed"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </Link>
                <Link 
                    v-if="$page.props.auth.user"
                    href="/posts/create" 
                    :class="['activity-icon', isActive('/posts/create') ? 'active' : '']"
                    title="Create Post"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </Link>
                <div class="flex-1"></div>
                <Link 
                    v-if="$page.props.auth.user"
                    href="#" 
                    class="activity-icon"
                    title="Account"
                    @click.prevent="showAccountMenu = !showAccountMenu"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </Link>
            </div>

            <!-- Sidebar -->
            <div class="sidebar bg-[#252526] w-64 flex flex-col border-r border-[#3e3e42] flex-shrink-0 overflow-hidden">
                <!-- Sidebar Header -->
                <div class="sidebar-header bg-[#2d2d30] px-3 py-2 border-b border-[#3e3e42] flex items-center justify-between">
                    <span class="text-xs font-semibold text-[#cccccc] uppercase tracking-wider">
                        {{ sidebarTitle }}
                    </span>
                    <button 
                        @click="sidebarCollapsed = !sidebarCollapsed"
                        class="text-[#cccccc] hover:text-white p-1"
                        title="Toggle Sidebar"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Sidebar Content -->
                <div v-if="!sidebarCollapsed" class="sidebar-content flex-1 overflow-y-auto">
                    <slot name="sidebar">
                        <!-- Default Sidebar Content -->
                        <div class="p-4 space-y-4">
                            <!-- Quick Links -->
                            <div>
                                <h3 class="text-xs font-semibold text-[#858585] uppercase mb-2 px-2">Quick Links</h3>
                                <div class="space-y-1">
                                    <Link href="/" class="sidebar-item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        <span>Home</span>
                                    </Link>
                                    <Link v-if="$page.props.auth.user" href="/feed" class="sidebar-item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        </svg>
                                        <span>My Feed</span>
                                    </Link>
                                    <Link v-if="$page.props.auth.user" href="/posts/create" class="sidebar-item">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span>Create Post</span>
                                    </Link>
                                </div>
                            </div>

                            <!-- User Profile -->
                            <div v-if="$page.props.auth.user">
                                <h3 class="text-xs font-semibold text-[#858585] uppercase mb-2 px-2">Profile</h3>
                                <div class="px-2 py-2">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <div class="w-8 h-8 bg-[#007acc] rounded flex items-center justify-center text-white text-xs font-bold">
                                            {{ $page.props.auth.user.name?.charAt(0).toUpperCase() || 'U' }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm text-[#cccccc] truncate">{{ $page.props.auth.user.name }}</p>
                                            <p class="text-xs text-[#858585] truncate">{{ $page.props.auth.user.email }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </slot>
                </div>
            </div>

            <!-- Main Editor Area -->
            <div class="editor-area flex-1 flex flex-col overflow-hidden">
                <!-- Tab Bar -->
                <div class="tab-bar bg-[#2d2d30] border-b border-[#3e3e42] flex items-center overflow-x-auto flex-shrink-0">
                    <!-- Page Tab (always visible) -->
                    <div 
                        :class="['tab', !activeTabId ? 'active' : '']"
                        @click="navigateToPage"
                    >
                        <span>{{ currentTabTitle }}</span>
                    </div>
                    
                    <!-- Post Tabs -->
                    <div
                        v-for="tab in tabs"
                        :key="tab.id"
                        :class="['tab', 'tab-post', activeTabId === tab.id ? 'active' : '']"
                        @click="setActiveTab(tab.id)"
                    >
                        <span class="truncate max-w-[200px]">{{ tab.title }}</span>
                        <button
                            @click.stop="removeTab(tab.id)"
                            class="tab-close ml-2 hover:bg-[#3e3e42] rounded p-0.5"
                            title="Close tab"
                        >
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Flash Messages -->
                <div v-if="$page.props.flash.message" class="px-4 py-2 bg-[#0e639c] text-[#ffffff] text-sm">
                    {{ $page.props.flash.message }}
                </div>
                <div v-if="$page.props.flash.error" class="px-4 py-2 bg-[#a1260d] text-[#ffffff] text-sm">
                    {{ $page.props.flash.error }}
                </div>

                <!-- Content Area -->
                <div class="content-area flex-1 overflow-y-auto bg-[#1e1e1e]">
                    <!-- Show active tab content or default slot -->
                    <div v-if="activeTabId && activeTab" class="h-full">
                        <PostTabContent :tab="activeTab" @close="removeTab(activeTab.id)" />
                    </div>
                    <div v-else class="h-full">
                        <slot />
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Bar (Bottom) -->
        <div class="status-bar bg-[#007acc] text-white text-xs h-6 flex items-center px-2 justify-between flex-shrink-0">
            <div class="flex items-center space-x-4">
                <span v-if="$page.props.auth.user" class="flex items-center space-x-1">
                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ $page.props.auth.user.name }}</span>
                </span>
                <span v-else class="flex items-center space-x-1">
                    <Link href="/login" class="hover:underline">Login</Link>
                    <span>|</span>
                    <Link href="/register" class="hover:underline">Sign Up</Link>
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <span>Ln 1, Col 1</span>
                <span>Spaces: 4</span>
                <span>UTF-8</span>
                <span>LF</span>
                <span>Markdown</span>
            </div>
        </div>

        <!-- Account Menu Dropdown -->
        <div 
            v-if="showAccountMenu" 
            class="fixed right-2 top-12 bg-[#3c3c3c] border border-[#454545] shadow-lg rounded z-50 min-w-[200px]"
            @click.stop
        >
            <div class="py-1">
                <div class="px-4 py-2 text-sm text-[#cccccc] border-b border-[#454545]">
                    {{ $page.props.auth.user?.name }}
                </div>
                <Link href="/profile" class="block px-4 py-2 text-sm text-[#cccccc] hover:bg-[#2a2d2e]">
                    Profile
                </Link>
                <form @submit.prevent="logout" class="block">
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-[#cccccc] hover:bg-[#2a2d2e]">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <!-- Backdrop for account menu -->
        <div 
            v-if="showAccountMenu" 
            class="fixed inset-0 z-40" 
            @click="showAccountMenu = false"
        ></div>
    </div>
</template>

<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed, provide, watch, onMounted, onUnmounted } from 'vue';
import { useTabs, type Tab } from '@/composables/useTabs';
import PostTabContent from '@/Components/PostTabContent.vue';

const page = usePage();
const sidebarCollapsed = ref(false);
const showAccountMenu = ref(false);
const searchQuery = ref('');
const showSearchMenu = ref(false);
const canGoBack = ref(false);
const canGoForward = ref(false);
const isDarkMode = ref(true);

const { tabs, activeTabId, addTab, removeTab, setActiveTab, getActiveTab } = useTabs();

// Provide tab functions to child components
provide('addTab', addTab);
provide('tabs', tabs);
provide('activeTabId', activeTabId);

const currentPath = computed(() => {
    return (page.url as string) || '/';
});

const isActive = (path: string) => {
    return currentPath.value === path || currentPath.value.startsWith(path + '/');
};

const sidebarTitle = computed(() => {
    if (isActive('/feed')) return 'Feed';
    if (isActive('/posts/create')) return 'Create Post';
    return 'Explorer';
});

const currentTabTitle = computed(() => {
    if (isActive('/feed')) return 'Feed';
    if (isActive('/posts/create')) return 'Create Post';
    if (currentPath.value.includes('/posts/')) {
        return 'Post';
    }
    return 'Home';
});

const activeTab = computed(() => {
    return getActiveTab();
});

// Handle tab events from child components
const handleOpenPostInTab = (e: Event) => {
    const customEvent = e as CustomEvent;
    const { postId, postData, title } = customEvent.detail;
    const tab: Tab = {
        id: `post-${postId}`,
        title: title || `Post ${postId}`,
        type: 'post',
        postId,
        postData,
        url: `/posts/${postId}`,
    };
    addTab(tab);
};

// Set up event listener
onMounted(() => {
    if (typeof window !== 'undefined') {
        window.addEventListener('open-post-in-tab', handleOpenPostInTab);
    }
});

// Clean up event listener
onUnmounted(() => {
    if (typeof window !== 'undefined') {
        window.removeEventListener('open-post-in-tab', handleOpenPostInTab);
    }
});

const navigateToPage = () => {
    // Just set active tab to null to show page content (don't close tabs)
    setActiveTab(null);
    
    // Navigate to home if not already there
    if (currentPath.value !== '/') {
        router.visit('/', {
            preserveState: true, // Preserve tabs state
            preserveScroll: false,
        });
    }
    // If already on home, just switch to showing page content
};

const logout = () => {
    router.post('/logout');
    showAccountMenu.value = false;
};

const goBack = () => {
    window.history.back();
};

const goForward = () => {
    window.history.forward();
};

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        router.visit(`/search?q=${encodeURIComponent(searchQuery.value.trim())}`, {
            preserveState: false,
        });
    }
};

// Theme management
const toggleTheme = () => {
    isDarkMode.value = !isDarkMode.value;
    const html = document.documentElement;
    if (isDarkMode.value) {
        html.classList.add('dark');
        html.classList.remove('light');
        localStorage.setItem('theme', 'dark');
    } else {
        html.classList.add('light');
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

// Update navigation button states and initialize theme
onMounted(() => {
    // Initialize theme
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme) {
        isDarkMode.value = savedTheme === 'dark';
    } else {
        isDarkMode.value = prefersDark;
    }
    
    // Apply theme
    const html = document.documentElement;
    if (isDarkMode.value) {
        html.classList.add('dark');
        html.classList.remove('light');
    } else {
        html.classList.add('light');
        html.classList.remove('dark');
    }
    
    const updateNavState = () => {
        canGoBack.value = window.history.length > 1;
        canGoForward.value = false; // Browser doesn't expose forward state easily
    };
    updateNavState();
    window.addEventListener('popstate', updateNavState);
    
    onUnmounted(() => {
        window.removeEventListener('popstate', updateNavState);
    });
});
</script>

<style scoped>
.vscode-layout {
    font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
}

.activity-icon {
    width: 2.5rem;
    height: 2.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #858585;
    transition: color 0.2s, background-color 0.2s;
    margin-bottom: 0.25rem;
    border-radius: 0.25rem;
}

.activity-icon:hover {
    color: #ffffff;
    background-color: #37373d;
}

.activity-icon.active {
    color: #ffffff;
    border-left: 2px solid #007acc;
    background-color: #37373d;
}

.sidebar-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.375rem 0.5rem;
    font-size: 0.875rem;
    color: #cccccc;
    border-radius: 0.25rem;
    cursor: pointer;
    transition: background-color 0.2s;
}

.sidebar-item:hover {
    background-color: #2a2d2e;
}

.tab {
    padding: 0.375rem 1rem;
    font-size: 0.875rem;
    color: #969696;
    background-color: #2d2d30;
    border-right: 1px solid #3e3e42;
    cursor: pointer;
    transition: background-color 0.2s, color 0.2s;
}

.tab:hover {
    background-color: #1e1e1e;
}

.tab.active {
    background-color: #1e1e1e;
    color: #cccccc;
    border-bottom: 2px solid #007acc;
}

.tab-post {
    display: flex;
    align-items: center;
    padding-right: 0.5rem;
}

.tab-close {
    opacity: 0.7;
    transition: opacity 0.2s, background-color 0.2s;
}

.tab-close:hover {
    opacity: 1;
}

/* LinkedIn-style Navigation */
.top-nav-bar {
    min-height: 3.5rem;
}

.nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 0.75rem;
    color: #e7e9ec;
    text-decoration: none;
    transition: color 0.2s, background-color 0.2s;
    border-radius: 0.25rem;
    cursor: pointer;
    position: relative;
    min-width: 5rem;
}

.nav-item:hover {
    color: #ffffff;
    background-color: rgba(255, 255, 255, 0.05);
}

.nav-item.active {
    color: #ffffff;
}

.nav-item.active::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #ffffff;
    border-radius: 2px 2px 0 0;
}

.nav-label {
    font-size: 0.75rem;
    margin-top: 0.25rem;
    font-weight: 400;
    line-height: 1;
}

.nav-item svg {
    transition: color 0.2s;
}

.nav-item:hover svg {
    color: #ffffff;
}

.nav-item.active svg {
    color: #ffffff;
}

.content-area {
    scrollbar-width: thin;
    scrollbar-color: #424242 #1e1e1e;
}

.content-area::-webkit-scrollbar {
    width: 10px;
}

.content-area::-webkit-scrollbar-track {
    background: #1e1e1e;
}

.content-area::-webkit-scrollbar-thumb {
    background: #424242;
    border-radius: 5px;
}

.content-area::-webkit-scrollbar-thumb:hover {
    background: #4e4e4e;
}

.sidebar-content {
    scrollbar-width: thin;
    scrollbar-color: #424242 #252526;
}

.sidebar-content::-webkit-scrollbar {
    width: 10px;
}

.sidebar-content::-webkit-scrollbar-track {
    background: #252526;
}

.sidebar-content::-webkit-scrollbar-thumb {
    background: #424242;
    border-radius: 5px;
}

.sidebar-content::-webkit-scrollbar-thumb:hover {
    background: #4e4e4e;
}
</style>

