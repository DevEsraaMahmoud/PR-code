import { describe, it, expect, beforeEach, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import CodeViewer from '@/Components/CodeViewer.vue';

describe('CodeViewer', () => {
  const defaultProps = {
    snippet: {
      id: 1,
      language: 'javascript',
      content: 'const hello = "world";\nconsole.log(hello);',
    },
    isAuthenticated: true,
    canCopy: true,
  };

  beforeEach(() => {
    // Mock Prism
    global.Prism = {
      languages: {
        javascript: {},
        text: {},
      },
      highlight: vi.fn((code, grammar, lang) => {
        return `<span class="token keyword">${code}</span>`;
      }),
    };
  });

  it('renders code with line numbers', () => {
    const wrapper = mount(CodeViewer, {
      props: defaultProps,
    });

    expect(wrapper.text()).toContain('1');
    expect(wrapper.text()).toContain('2');
  });

  it('emits lineSelected when a line is clicked', async () => {
    const wrapper = mount(CodeViewer, {
      props: defaultProps,
    });

    const lineNumber = wrapper.find('[data-line-number="1"]');
    await lineNumber.trigger('click');

    expect(wrapper.emitted('lineSelected')).toBeTruthy();
    expect(wrapper.emitted('lineSelected')[0][0]).toEqual({ lineNumber: 1 });
  });

  it('copies code to clipboard when copy button is clicked', async () => {
    const writeTextMock = vi.fn().mockResolvedValue(undefined);
    global.navigator.clipboard = {
      writeText: writeTextMock,
    };

    const wrapper = mount(CodeViewer, {
      props: defaultProps,
    });

    const copyButton = wrapper.find('button[aria-label="Copy code to clipboard"]');
    await copyButton.trigger('click');

    expect(writeTextMock).toHaveBeenCalledWith(defaultProps.snippet.content);
    expect(wrapper.emitted('copySucceeded')).toBeTruthy();
  });

  it('highlights lines with comments', () => {
    const wrapper = mount(CodeViewer, {
      props: {
        ...defaultProps,
        inlineCommentsIndex: {
          '1': [{ id: 1, body: 'Test comment' }],
        },
      },
    });

    const lineWithComments = wrapper.find('.line-number.has-comments');
    expect(lineWithComments.exists()).toBe(true);
  });

  it('supports keyboard navigation', async () => {
    const wrapper = mount(CodeViewer, {
      props: defaultProps,
    });

    const container = wrapper.find('.code-viewer');
    await container.trigger('keydown', { key: 'ArrowDown' });
    await wrapper.vm.$nextTick();

    // Check that keyboard selection is updated
    expect(wrapper.vm.keyboardSelectedLine).toBe(2);
  });

  it('opens composer on Enter key', async () => {
    const wrapper = mount(CodeViewer, {
      props: defaultProps,
    });

    wrapper.vm.keyboardSelectedLine = 1;
    await wrapper.vm.$nextTick();

    const container = wrapper.find('.code-viewer');
    await container.trigger('keydown', { key: 'Enter' });

    expect(wrapper.emitted('lineSelected')).toBeTruthy();
  });

  it('does not allow line clicks when not authenticated', async () => {
    const wrapper = mount(CodeViewer, {
      props: {
        ...defaultProps,
        isAuthenticated: false,
      },
    });

    const lineNumber = wrapper.find('[data-line-number="1"]');
    await lineNumber.trigger('click');

    expect(wrapper.emitted('lineSelected')).toBeFalsy();
  });
});


