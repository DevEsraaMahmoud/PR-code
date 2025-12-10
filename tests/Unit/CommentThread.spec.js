import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import CommentThread from '@/Components/CommentThread.vue';

describe('CommentThread', () => {
  const defaultProps = {
    thread: {
      lineNumber: 10,
      blockId: 1,
      postId: 1,
      messages: [
        {
          id: 1,
          body: 'This is a test comment',
          user: { id: 1, name: 'Test User' },
          created_at: new Date().toISOString(),
        },
      ],
      resolved: false,
    },
    currentUserId: 1,
    canResolve: true,
  };

  beforeEach(() => {
    // Mock fetch for API calls
    global.fetch = vi.fn();
  });

  it('renders thread messages', () => {
    const wrapper = mount(CommentThread, {
      props: defaultProps,
      global: {
        stubs: {
          Reactions: true, // Stub Reactions component
        },
      },
    });

    expect(wrapper.text()).toContain('Line 10');
    expect(wrapper.text()).toContain('This is a test comment');
    expect(wrapper.text()).toContain('Test User');
  });

  it('shows resolved status when thread is resolved', () => {
    const wrapper = mount(CommentThread, {
      props: {
        ...defaultProps,
        thread: {
          ...defaultProps.thread,
          resolved: true,
        },
      },
      global: {
        stubs: {
          Reactions: true,
        },
      },
    });

    expect(wrapper.text()).toContain('Resolved');
  });

  it('allows replying to a comment', async () => {
    const wrapper = mount(CommentThread, {
      props: defaultProps,
      global: {
        stubs: {
          Reactions: true,
        },
      },
    });

    const replyButton = wrapper.find('button:contains("Reply")');
    await replyButton.trigger('click');

    const textarea = wrapper.find('textarea[placeholder*="Write a reply"]');
    expect(textarea.exists()).toBe(true);
  });

  it('performs optimistic update when replying', async () => {
    const wrapper = mount(CommentThread, {
      props: defaultProps,
      global: {
        stubs: {
          Reactions: true,
        },
      },
    });

    // Open reply form
    const replyButton = wrapper.find('button:contains("Reply")');
    await replyButton.trigger('click');
    await wrapper.vm.$nextTick();

    // Enter reply text
    const textarea = wrapper.find('textarea[placeholder*="Write a reply"]');
    await textarea.setValue('This is a reply');

    // Submit reply
    const submitButton = wrapper.find('button:contains("Reply")');
    await submitButton.trigger('click');
    await wrapper.vm.$nextTick();

    // Check that reply event was emitted
    expect(wrapper.emitted('reply')).toBeTruthy();
    expect(wrapper.emitted('reply')[0]).toEqual([1, 'This is a reply']);
  });

  it('allows editing own comments', async () => {
    const wrapper = mount(CommentThread, {
      props: defaultProps,
      global: {
        stubs: {
          Reactions: true,
        },
      },
    });

    const editButton = wrapper.find('button:contains("Edit")');
    await editButton.trigger('click');
    await wrapper.vm.$nextTick();

    const textarea = wrapper.find('textarea');
    expect(textarea.exists()).toBe(true);
    expect(textarea.element.value).toContain('This is a test comment');
  });

  it('allows resolving thread', async () => {
    const wrapper = mount(CommentThread, {
      props: defaultProps,
      global: {
        stubs: {
          Reactions: true,
        },
      },
    });

    const resolveButton = wrapper.find('button:contains("Mark Resolved")');
    await resolveButton.trigger('click');
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('resolve')).toBeTruthy();
  });

  it('rolls back optimistic update on error', async () => {
    const wrapper = mount(CommentThread, {
      props: defaultProps,
      global: {
        stubs: {
          Reactions: true,
        },
      },
    });

    const initialMessageCount = wrapper.vm.thread.messages.length;

    // Open reply form and submit
    const replyButton = wrapper.find('button:contains("Reply")');
    await replyButton.trigger('click');
    await wrapper.vm.$nextTick();

    const textarea = wrapper.find('textarea[placeholder*="Write a reply"]');
    await textarea.setValue('This is a reply');

    // Mock API failure
    wrapper.vm.$emit = vi.fn().mockImplementation((event, ...args) => {
      if (event === 'reply') {
        throw new Error('API Error');
      }
    });

    const submitButton = wrapper.find('button:contains("Reply")');
    await submitButton.trigger('click').catch(() => {});

    // Check that optimistic update was rolled back
    await wrapper.vm.$nextTick();
    expect(wrapper.vm.thread.messages.length).toBe(initialMessageCount);
  });
});


