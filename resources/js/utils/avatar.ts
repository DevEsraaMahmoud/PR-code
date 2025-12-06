/**
 * Avatar utility functions
 * Provides fallback avatars when user avatar is not available
 */

/**
 * Generate a placeholder avatar URL using UI Avatars service
 * @param name - User's name or initials
 * @param size - Avatar size (default: 64)
 */
export function getAvatarUrl(avatarUrl: string | undefined | null, name?: string, size: number = 64): string {
  if (avatarUrl) {
    return avatarUrl;
  }

  // Use UI Avatars service for placeholder
  const initials = name
    ? name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
    : '?';

  // Generate a color based on the name
  const colors = [
    '4F46E5', // indigo
    '7C3AED', // purple
    'EC4899', // pink
    'F59E0B', // amber
    '10B981', // emerald
    '3B82F6', // blue
    'EF4444', // red
    '8B5CF6', // violet
  ];
  
  const colorIndex = name
    ? name.charCodeAt(0) % colors.length
    : 0;
  
  const color = colors[colorIndex];

  return `https://ui-avatars.com/api/?name=${encodeURIComponent(initials)}&size=${size}&background=${color}&color=fff&bold=true`;
}

/**
 * Generate a data URI for a simple colored circle avatar
 * @param name - User's name or initials
 * @param size - Avatar size (default: 64)
 */
export function generateDataUriAvatar(name?: string, size: number = 64): string {
  const initials = name
    ? name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2)
    : '?';

  const colors = [
    '#4F46E5', // indigo
    '#7C3AED', // purple
    '#EC4899', // pink
    '#F59E0B', // amber
    '#10B981', // emerald
    '#3B82F6', // blue
    '#EF4444', // red
    '#8B5CF6', // violet
  ];
  
  const colorIndex = name
    ? name.charCodeAt(0) % colors.length
    : 0;
  
  const color = colors[colorIndex];

  // Create SVG data URI
  const svg = `
    <svg width="${size}" height="${size}" xmlns="http://www.w3.org/2000/svg">
      <circle cx="${size / 2}" cy="${size / 2}" r="${size / 2}" fill="${color}"/>
      <text x="50%" y="50%" font-family="Arial, sans-serif" font-size="${size / 2.5}" font-weight="bold" fill="white" text-anchor="middle" dominant-baseline="central">${initials}</text>
    </svg>
  `.trim();

  return `data:image/svg+xml;base64,${btoa(svg)}`;
}

