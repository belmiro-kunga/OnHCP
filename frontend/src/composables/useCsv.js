// Utility composable for simple CSV generation
// Exports: generateCSV(data: Array<object>) -> string
// - Escapes values with quotes, doubles inner quotes, and joins with commas.
// - Uses object keys order of the first row if headers are not provided.

function escapeCsvValue(value) {
  if (value == null) return ''
  let str = String(value)
  // Normalize newlines
  str = str.replace(/\r\n|\r|\n/g, '\n')
  // Escape quotes by doubling them
  if (/[",\n]/.test(str)) {
    str = '"' + str.replace(/"/g, '""') + '"'
  }
  return str
}

export function generateCSV(rows) {
  const data = Array.isArray(rows) ? rows : []
  if (data.length === 0) return ''

  // Determine headers from union of keys to be robust
  const headerSet = new Set()
  data.forEach((row) => Object.keys(row || {}).forEach((k) => headerSet.add(k)))
  const headers = Array.from(headerSet)

  const csvRows = []
  csvRows.push(headers.map(escapeCsvValue).join(','))

  data.forEach((row) => {
    const line = headers.map((h) => escapeCsvValue(row?.[h]))
    csvRows.push(line.join(','))
  })

  return csvRows.join('\n')
}
