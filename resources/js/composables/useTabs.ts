import { ref, reactive } from 'vue';

export interface Tab {
  id: string;
  title: string;
  type: 'page' | 'post';
  url?: string;
  postId?: number | string;
  postData?: any;
}

const tabs = ref<Tab[]>([]);
const activeTabId = ref<string | null>(null);

export function useTabs() {
  const addTab = (tab: Tab) => {
    // Check if tab already exists
    const existingTab = tabs.value.find(t => t.id === tab.id);
    if (existingTab) {
      activeTabId.value = tab.id;
      return;
    }
    
    tabs.value.push(tab);
    activeTabId.value = tab.id;
  };

  const removeTab = (tabId: string) => {
    const index = tabs.value.findIndex(t => t.id === tabId);
    if (index === -1) return;

    tabs.value.splice(index, 1);

    // If we removed the active tab, switch to another tab
    if (activeTabId.value === tabId) {
      if (tabs.value.length > 0) {
        // Switch to the tab that was before the removed one, or the first tab
        activeTabId.value = tabs.value[Math.max(0, index - 1)]?.id || null;
      } else {
        activeTabId.value = null;
      }
    }
  };

  const setActiveTab = (tabId: string | null) => {
    if (tabId === null) {
      activeTabId.value = null;
      return;
    }
    if (tabs.value.find(t => t.id === tabId)) {
      activeTabId.value = tabId;
    }
  };

  const getActiveTab = () => {
    return tabs.value.find(t => t.id === activeTabId.value) || null;
  };

  return {
    tabs,
    activeTabId,
    addTab,
    removeTab,
    setActiveTab,
    getActiveTab,
  };
}

