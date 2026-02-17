# CE.SDK Video Timeline & Chapter Selection Prompt

Use this prompt with the `cesdk-docs-js` skill to implement a video timeline with chapter selection functionality.

---

## Prompt for CE.SDK Implementation

```
I need to implement a video timeline editor using CE.SDK Vanilla JavaScript for a Laravel Filament application. 

Requirements:
1. Display a video timeline that shows the video duration and current playback position
2. Allow users to select a time range (from & to) on the timeline by clicking and dragging
3. Visualize selected regions/chapters on the timeline as colored blocks
4. Create new chapters from selected time ranges with editable titles
5. Display existing chapters as visual blocks on the timeline
6. Sync timeline playback with the video player
7. Allow users to click on chapter blocks to jump to that time in the video

Current implementation uses WaveSurfer.js but I want to migrate to CE.SDK's native timeline features.

Please use CE.SDK documentation to:
- Show me how to create a video scene and display the timeline
- Explain how to handle timeline selection (region selection from & to)
- Provide code examples for creating and managing video chapters/markers
- Show how to sync timeline playhead with video playback
- Explain how to render chapter blocks visually on the timeline
- Show how to handle user interactions (click, drag, select) on the timeline

Focus on:
- Timeline editor API and methods
- Region/marker creation and management
- Playback control synchronization
- Visual representation of chapters on timeline
- User interaction handling (selection, dragging, clicking)
```

---

## Alternative Shorter Prompt

```
Using CE.SDK Vanilla JavaScript, help me create a video timeline editor where:
- Users can see the video timeline with a playhead
- Users can select a time range (from & to) by clicking and dragging on the timeline
- Selected ranges become chapters that appear as visual blocks
- Users can edit chapter titles
- Timeline playback syncs with video player

Show me the CE.SDK timeline editor API, region selection methods, and how to create/manage video markers/chapters.
```

---

## Specific Feature Prompts

### For Timeline Display
```
Using CE.SDK docs-js: How do I display a video timeline with playhead and time markers? Show me the timeline editor API and how to render it in a web page.
```

### For Region Selection
```
Using CE.SDK docs-js: How can users select a time range (from & to) on the video timeline? Show me how to handle region selection, dragging, and getting start/end times.
```

### For Chapter Creation
```
Using CE.SDK docs-js: How do I create video chapters/markers at specific time ranges? Show me how to add, edit, and manage chapters on the timeline.
```

### For Playback Sync
```
Using CE.SDK docs-js: How do I synchronize the timeline playhead with video playback? Show me how to update the playhead position as the video plays.
```

### For Visual Chapter Blocks
```
Using CE.SDK docs-js: How do I render chapter blocks visually on the timeline? Show me how to display existing chapters as colored regions on the timeline.
```

---

## Usage Instructions

1. **Copy one of the prompts above** (use the main prompt for comprehensive help, or specific prompts for focused questions)

2. **Paste it in Cursor** - The `cesdk-docs-js` skill will automatically activate when you mention CE.SDK, timeline, or video editing

3. **The skill will provide:**
   - Relevant CE.SDK documentation
   - Code examples from the official docs
   - API references for timeline features
   - Best practices for implementation

4. **Follow up with specific questions** like:
   - "Show me the exact code for region selection"
   - "How do I get the start and end time of a selected region?"
   - "How do I create a chapter marker at a specific time?"

---

## Integration Notes

Your current implementation is in:
- `resources/views/filament/resources/video-resource/pages/edit-video-chapters.blade.php`

The CE.SDK skill will help you:
- Replace WaveSurfer.js with CE.SDK's native timeline
- Use CE.SDK's video editing capabilities
- Implement proper chapter management
- Handle timeline interactions correctly

---

## Quick Start Example

```
I'm building a video chapter editor in Laravel Filament. I have a video player and need to add CE.SDK timeline below it. Users should be able to:
1. See the video timeline with playhead
2. Click and drag to select a time range
3. Create a chapter from the selected range
4. See all chapters as blocks on the timeline

Using CE.SDK Vanilla JavaScript, show me:
- How to initialize the timeline editor
- How to handle region selection
- How to create chapters from selections
- How to display existing chapters on the timeline
```
