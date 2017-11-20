
export function localStoreSupport () {
  const mod = 'test'
  try {
    localStorage.setItem(mod, mod)
    localStorage.removeItem(mod)
    return true
  } catch (e) {
    return false
  }
}

export function setStorage(name, value) {
  const expires = ''

  if(localStoreSupport()) {
    localStorage.setItem(name, value)
  } else {
    document.cookie = name + '=' + value + expires + '; path=/'
  }
}

export function getStorage(name) {
  if(localStoreSupport()) {
    return localStorage.getItem(name)
  }
  else {
    const name = name + '='
    const ca = document.cookie.split(';')
    for(var i = 0; i < ca.length; i++) {
      const c = ca[i]
      while (c.charAt(0) == ' ') c = c.substring(1,c.length)
      if (c.indexOf(name) == 0) return c.substring(name.length,c.length)
    }
    return null
  }
}
