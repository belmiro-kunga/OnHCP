// Utility composable for sanitization and display helpers
// Provides: stripHtml, displayEntityName, displayOptionLabel

export function stripHtml(value) {
  if (typeof value !== 'string') return value ?? ''
  const tmp = document.createElement('div')
  tmp.innerHTML = value
  return (tmp.textContent || tmp.innerText || '').trim()
}

// Normalize value from backend (string/JSON/object) to extract a displayable name
export function displayEntityName(value) {
  try {
    if (value == null) return ''
    if (typeof value === 'string') {
      const trimmed = value.trim()
      if ((trimmed.startsWith('{') && trimmed.endsWith('}')) || (trimmed.startsWith('[') && trimmed.endsWith(']'))) {
        try {
          const parsed = JSON.parse(trimmed)
          const name = parsed?.name ?? parsed?.label ?? parsed?.title ?? parsed?.description ?? ''
          return stripHtml(String(name || ''))
        } catch (_) {
          return stripHtml(value)
        }
      }
      return stripHtml(value)
    }
    if (typeof value === 'object') {
      const name = value?.name ?? value?.label ?? value?.title ?? value?.description ?? ''
      return stripHtml(String(name || ''))
    }
    return stripHtml(String(value))
  } catch (_) {
    return ''
  }
}

// Normalize and sanitize option labels (avoid JSON/objects showing in UI)
export function displayOptionLabel(option) {
  try {
    if (option == null) return ''
    const labelSource = option.label ?? option.name ?? option.title ?? option.description ?? ''
    if (typeof labelSource === 'string') {
      const trimmed = labelSource.trim()
      if ((trimmed.startsWith('{') && trimmed.endsWith('}')) || (trimmed.startsWith('[') && trimmed.endsWith(']'))) {
        try {
          const parsed = JSON.parse(trimmed)
          const fromParsed = parsed?.name ?? parsed?.label ?? parsed?.title ?? parsed?.description ?? ''
          return stripHtml(String(fromParsed || ''))
        } catch (_) {
          return stripHtml(labelSource)
        }
      }
      return stripHtml(labelSource)
    }
    if (typeof labelSource === 'object' && labelSource !== null) {
      const fromObj = labelSource.name ?? labelSource.label ?? labelSource.title ?? labelSource.description ?? ''
      return stripHtml(String(fromObj || ''))
    }
    return stripHtml(String(labelSource))
  } catch (_) {
    return ''
  }
}
