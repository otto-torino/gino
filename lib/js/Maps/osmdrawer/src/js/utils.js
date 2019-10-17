export function capitalize (str) {
  return str.charAt(0).toUpperCase() + str.slice(1)
}

export function sprintf (str, ...args) {
  return str.replace(/{(\d+)}/g, function (match, number) {
    return typeof args[number] !== 'undefined'
      ? args[number]
      : match
  })
}
