<template>
  <div class="notifications-dropdown relative">
    <!-- Trigger Button -->
    <button
      ref="triggerRef"
      @click="toggleDropdown"
      class="relative p-2 text-[#858585] hover:text-[#cccccc] transition-colors rounded hover:bg-[#3e3e42]"
      :aria-label="`Notifications${unreadCount > 0 ? ` (${unreadCount} unread)` : ''}`"
      :aria-expanded="isOpen"
    >
      <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 w-4 h-4 bg-red-500 rounded-full text-white text-[10px] flex items-center justify-center font-bold"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown Panel -->
    <div
      v-if="isOpen"
      ref="dropdownRef"
      class="absolute right-0 top-full mt-2 w-80 bg-[#252526] border border-[#3e3e42] rounded-lg shadow-xl z-50 max-h-96 flex flex-col"
      role="menu"
      aria-label="Notifications"
    >
      <!-- Header -->
      <div class="flex items-center justify-between px-4 py-3 border-b border-[#3e3e42] flex-shrink-0">
        <h3 class="text-sm font-semibold text-[#cccccc]">Notifications</h3>
        <button
          v-if="notifications.length > 0"
          @click="markAllAsRead"
          class="text-xs text-[#007acc] hover:text-[#4fc3f7] transition-colors"
        >
          Mark all as read
        </button>
      </div>

      <!-- Notifications List -->
      <div class="flex-1 overflow-y-auto">
        <div v-if="notifications.length === 0" class="px-4 py-8 text-center">
          <p class="text-sm text-[#858585]">No notifications</p>
        </div>

        <div v-else class="divide-y divide-[#3e3e42]">
          <div
            v-for="notification in notifications"
            :key="notification.id"
            class="px-4 py-3 hover:bg-[#2a2d2e] transition-colors cursor-pointer"
            :class="{ 'bg-[#094771]': !notification.read_at }"
            @click="handleNotificationClick(notification)"
            role="menuitem"
          >
            <div class="flex items-start gap-3">
              <div
                class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0"
                :style="{ backgroundColor: getNotificationColor(notification.type) }"
              >
                {{ getNotificationIcon(notification.type) }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <span class="text-xs font-medium text-[#cccccc]">
                    {{ notification.title || getNotificationTitle(notification.type) }}
                  </span>
                  <span
                    v-if="!notification.read_at"
                    class="w-2 h-2 bg-[#007acc] rounded-full"
                    title="Unread"
                  ></span>
                </div>
                <p class="text-xs text-[#858585] line-clamp-2">
                  {{ notification.message || notification.body }}
                </p>
                <p class="text-xs text-[#858585] mt-1">
                  {{ formatDate(notification.created_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div v-if="notifications.length > 0" class="px-4 py-3 border-t border-[#3e3e42] flex-shrink-0">
        <router-link
          to="/notifications"
          class="block text-center text-xs text-[#007acc] hover:text-[#4fc3f7] transition-colors"
          @click="isOpen = false"
        >
          View all notifications →
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
  notifications?: Array<{
    id: number | string;
    type: string;
    title?: string;
    message?: string;
    body?: string;
    read_at?: string | null;
    created_at: string;
    data?: any;
  }>;
  maxItems?: number;
}>();

const emit = defineEmits<{
  'notification-clicked': [notification: any];
  'mark-read': [notificationId: number | string];
  'mark-all-read': [];
}>();

const isOpen = ref(false);
const triggerRef = ref<HTMLElement>();
const dropdownRef = ref<HTMLElement>();

const notifications = computed(() => {
  const items = props.notifications || [];
  return items.slice(0, props.maxItems || 10);
});

const unreadCount = computed(() => {
  return notifications.value.filter(n => !n.read_at).length;
});

function toggleDropdown() {
  isOpen.value = !isOpen.value;
}

function getNotificationIcon(type: string): string {
  const icons: Record<string, string> = {
    comment: '💬',
    reaction: '👍',
    mention: '@',
    follow: '👤',
    post: '📝',
  };
  return icons[type] || '🔔';
}

function getNotificationColor(type: string): string {
  const colors: Record<string, string> = {
    comment: '#007acc',
    reaction: '#4fc3f7',
    mention: '#569cd6',
    follow: '#9cdcfe',
    post: '#dcdcaa',
  };
  return colors[type] || '#858585';
}

function getNotificationTitle(type: string): string {
  const titles: Record<string, string> = {
    comment: 'New comment',
    reaction: 'New reaction',
    mention: 'You were mentioned',
    follow: 'New follower',
    post: 'New post',
  };
  return titles[type] || 'Notification';
}

function formatDate(dateString: string): string {
  const date = new Date(dateString);
  const now = new Date();
  const diffMs = now.getTime() - date.getTime();
  const diffMins = Math.floor(diffMs / 60000);
  const diffHours = Math.floor(diffMs / 3600000);
  const diffDays = Math.floor(diffMs / 86400000);

  if (diffMins < 1) return 'just now';
  if (diffMins < 60) return `${diffMins}m ago`;
  if (diffHours < 24) return `${diffHours}h ago`;
  if (diffDays < 7) return `${diffDays}d ago`;
  return date.toLocaleDateString();
}

async function handleNotificationClick(notification: any) {
  // Mark as read if unread
  if (!notification.read_at) {
    await markAsRead(notification.id);
  }
  
  emit('notification-clicked', notification);
  
  // Navigate based on notification type
  if (notification.data?.post_id) {
    router.visit(`/posts/${notification.data.post_id}`);
  } else if (notification.data?.user_id) {
    router.visit(`/profile/${notification.data.user_id}`);
  }
  
  isOpen.value = false;
}

async function markAsRead(notificationId: number | string) {
  emit('mark-read', notificationId);
  
  // TODO: Call API to mark as read
  // await axios.patch(`/api/v1/notifications/${notificationId}/read`);
}

async function markAllAsRead() {
  emit('mark-all-read');
  
  // TODO: Call API to mark all as read
  // await axios.patch('/api/v1/notifications/read-all');
}

function handleClickOutside(event: MouseEvent) {
  if (
    dropdownRef.value &&
    triggerRef.value &&
    !dropdownRef.value.contains(event.target as Node) &&
    !triggerRef.value.contains(event.target as Node)
  ) {
    isOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.notifications-dropdown {
  position: relative;
}
</style>


