/**
 * Shoutbox
 */
const shoutbox = {
  refreshInterval: shoutbox2024_conf.refreshInterval,
  ajaxURL: shoutbox2024_conf.ajaxURL,
  fetching: false,
  messagePMTo: null,
  messages: [],
  smilies: [],
  init: async () => {
    // Add loading spinner to display while shoutbox is loading
    shoutbox.loader(true)
    await shoutbox.getSmilies()
    await shoutbox.refresh()
    shoutbox.setupEventHandlers()

    setInterval(async () => {
      await shoutbox.refresh()
    }, shoutbox.refreshInterval * 1000)
    shoutbox.loader(false)
  },
  loader: (show) => {
    const shoutboxLoader = document.getElementById('shoutbox-shouts-loading')
    if (show) {
      shoutboxLoader.style.display = 'flex'
    } else {
      shoutboxLoader.style.display = 'none'
    }
  },
  setupEventHandlers: () => {
    document.getElementById('shoutbox-refresh-button').addEventListener('click', shoutbox.refresh)
    document.getElementById('shoutbox-submit-button').addEventListener('click', shoutbox.submitShout)
    document.getElementById('shoutbox-clear-button').addEventListener('click', shoutbox.clearShoutInput)
    document.getElementById('shoutbox-smilies-button').addEventListener('click', shoutbox.displaySmilies)
    // Preferences Button
    document.getElementById('shoutbox-preferences-button').addEventListener('click', shoutbox.preferences)

    // Detect "enter" key press in message input
    document.getElementById('shoutbox-global-shout').addEventListener('keypress', (event) => {
      if (event.key === 'Enter') {
        shoutbox.submitShout()
      }
    })
  },
  preferences: () => {
    // Fetch user preferences from local storage
    const shoutboxPreferencesCSS = localStorage.getItem('shoutboxPreferencesCSS')

    const preferencesContainer = document.getElementById('shoutbox-preferences-container')
    preferencesContainer.innerHTML = ''

    // Add close button to preferences container
    const closeButton = document.createElement('button')
    closeButton.innerHTML = 'Close'
    closeButton.addEventListener('click', () => {
      preferencesContainer.style.display = 'none'
      preferencesContainer.innerHTML = ''
    })
    preferencesContainer.appendChild(closeButton)

    // Add preferences form
    const preferencesForm = document.createElement('form')
    preferencesForm.id = 'shoutbox-preferences-form'
    preferencesForm.innerHTML = `
    <table>
        <tr>
            <td>Font Colour:</td>
            <td>
                <input type="color" id="shoutbox-font-colour" name="shoutbox-font-colour" value="${shoutbox2024_conf.defaultColour}">
            </td>
        </tr>
        <tr>
            <td>Font Weight:</td>
            <td>
                <select id="shoutbox-font-weight" name="shoutbox-font-weight">
                    <option value="normal">Normal</option>
                    <option value="bold">Bold</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Font Style:</td>
            <td>
                <select id="shoutbox-font-style" name="shoutbox-font-style">
                    <option value="normal">Normal</option>
                    <option value="italic">Italic</option>
                </select>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <button type="button" id="shoutbox-preferences-save-button">Save</button>
            </td>
        </tr>
    </table>`
    preferencesContainer.appendChild(preferencesForm)

    // Set form values from local storage
    if (shoutboxPreferencesCSS) {
      const preferences = shoutboxPreferencesCSS.split(';')
      const fontColour = preferences[0].split(': ')[1]
      const fontWeight = preferences[1].split(': ')[1]
      const fontStyle = preferences[2].split(': ')[1]

      document.getElementById('shoutbox-font-colour').value = fontColour
      document.getElementById('shoutbox-font-weight').value = fontWeight
      document.getElementById('shoutbox-font-style').value = fontStyle
    }

    // Add event handler to save button
    document.getElementById('shoutbox-preferences-save-button').addEventListener('click', shoutbox.savePrefs)
    preferencesContainer.style.display = 'block'
  },
  savePrefs: () => {
    const preferencesContainer = document.getElementById('shoutbox-preferences-container')

    const newFontColour = document.getElementById('shoutbox-font-colour').value
    const newFontWeight = document.getElementById('shoutbox-font-weight').value
    const newFontStyle = document.getElementById('shoutbox-font-style').value

    const shoutboxPreferencesCSS = `color: ${newFontColour}; font-weight: ${newFontWeight}; font-style: ${newFontStyle};`
    localStorage.setItem('shoutboxPreferencesCSS', shoutboxPreferencesCSS)

    preferencesContainer.style.display = 'none'
    preferencesContainer.innerHTML = ''
  },
  getSmilies: async () => {
    if (shoutbox.fetching) {
      // try again in 2 seconds
      setTimeout(shoutbox.getSmilies, 2000)
      return
    }
    shoutbox.fetching = true
    const formdata = new FormData()
    formdata.append('action', 'shoutbox2024_get_smilies')
    formdata.append('nonce', shoutbox2024_conf.ajaxNonce)

    const requestOptions = {
      method: 'POST',
      body: formdata,
      redirect: 'follow'
    }

    await fetch(`${shoutbox.ajaxURL}`, requestOptions)
      .then((response) => response.json())
      .then((result) => {
        shoutbox.smilies = result
        shoutbox.fetching = false
      })
      .catch((error) => console.error(error))
  },
  displaySmilies: () => {
    // If no smilies, return
    if (shoutbox.smilies.length === 0) {
      return
    }

    const smiliesContainer = document.getElementById('shoutbox-smilies-container')
    smiliesContainer.innerHTML = ''

    // Add close button to smilie container
    const closeButton = document.createElement('button')
    closeButton.innerHTML = 'Close'
    closeButton.addEventListener('click', () => {
      smiliesContainer.style.display = 'none'
      smiliesContainer.innerHTML = ''
    })
    smiliesContainer.appendChild(closeButton)

    shoutbox.smilies.forEach((smiley) => {
      const smileyHTML = `<img src="${smiley.url}" alt="${smiley.code}" title="${smiley.name}" onClick="shoutbox.insertSmiley('${smiley.code}')" />`
      smiliesContainer.insertAdjacentHTML('beforeend', smileyHTML)
    })

    smiliesContainer.style.display = 'block'
  },
  insertSmiley: (smileyCode) => {
    const messageInput = document.getElementById('shoutbox-global-shout')
    const currentMessage = messageInput.value
    messageInput.value = currentMessage + ' ' + smileyCode + ' '
    document.getElementById('shoutbox-smilies-container').style.display = 'none'
    messageInput.focus()
  },
  parseSmilies: (message) => {
    shoutbox.smilies.forEach((smiley) => {
      const regex = new RegExp(smiley.code, 'g')
      const smileyHTML = `<img src="${smiley.url}" alt="${smiley.code}" title="${smiley.name}" />`
      message = message.replace(regex, smileyHTML)
    })

    return message
  },
  refresh: async () => {
    // disable refresh button until finished
    document.getElementById('shoutbox-refresh-button').disabled = true
    await shoutbox.getMessages().then(shoutbox.insertFetchedMessagesToDOM)

    // enable refresh button
    document.getElementById('shoutbox-refresh-button').disabled = false

    // Scroll to bottom of the shoutbox table
    const shoutboxTable = document.getElementById('shoutbox-shouts-table')
    shoutboxTable.parentNode.scrollTop = shoutboxTable.offsetHeight
  },
  focus: () => {
    document.getElementById('shoutbox-global-shout').focus()
  },
  getMessages: () => {
    if (shoutbox.fetching) {
      // try again in 2 seconds
      setTimeout(shoutbox.getMessages, 2000)
      return
    }

    const formdata = new FormData()
    formdata.append('action', 'shoutbox2024_get_shouts')
    formdata.append('nonce', shoutbox2024_conf.ajaxNonce)

    const requestOptions = {
      method: 'POST',
      body: formdata,
      redirect: 'follow'
    }

    shoutbox.fetching = true

    return fetch(`${shoutbox.ajaxURL}`, requestOptions)
      .then((response) => response.json())
      .then((result) => {
        shoutbox.messages = result
        shoutbox.fetching = false
      })
      .catch((error) => console.error(error))
  },
  formatTimestamp: (timestamp) => {
    const messageDate = new Date(timestamp)
    const now = new Date()

    // Clear the time components of both dates
    const messageDateWithoutTime = new Date(messageDate.getFullYear(), messageDate.getMonth(), messageDate.getDate())
    const nowWithoutTime = new Date(now.getFullYear(), now.getMonth(), now.getDate())

    const diff = nowWithoutTime - messageDateWithoutTime
    const diffDays = Math.floor(diff / (1000 * 60 * 60 * 24))

    if (diffDays === 0) {
      // Today
      const formattedTime = messageDate.toLocaleTimeString(shoutbox2024_conf.locale, { hour: '2-digit', minute: '2-digit', timeZone: shoutbox2024_conf.timezone })
      return '(Today, ' + formattedTime + ')'
    } else if (diffDays === 1) {
      // Yesterday
      const formattedTime = messageDate.toLocaleTimeString(shoutbox2024_conf.locale, { hour: '2-digit', minute: '2-digit', timeZone: shoutbox2024_conf.timezone })
      return '(Yesterday, ' + formattedTime + ')'
    } else if (diffDays < 7) {
      // Within a week
      const formattedDate = messageDate.toLocaleDateString(shoutbox2024_conf.locale, { weekday: 'long' })
      const formattedTime = messageDate.toLocaleTimeString(shoutbox2024_conf.locale, { hour: '2-digit', minute: '2-digit', timeZone: shoutbox2024_conf.timezone })
      return '(' + formattedDate + ', ' + formattedTime + ')'
    } else {
      // More than a week ago
      const formattedDateTime = messageDate.toLocaleString(shoutbox2024_conf.locale, { day: 'numeric', month: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit', timeZone: shoutbox2024_conf.timezone })
      return formattedDateTime
    }
  },
  sendPMtoUser: (event, userID, displayName) => {
    event.preventDefault()
    // Populate the PM container
    const pmContainer = document.getElementById('shoutbox-pm-container')
    const pmTo = document.getElementById('shoutbox-pm-to')

    pmTo.innerHTML = displayName
    pmContainer.style.display = 'block'

    // Add event handler to close button
    const closeButton = document.getElementById('shoutbox-pm-cancel-button')
    closeButton.addEventListener('click', shoutbox.closePMContainer)
    shoutbox.messagePMTo = userID
  },
  closePMContainer: () => {
    const pmContainer = document.getElementById('shoutbox-pm-container')
    shoutbox.messagePMTo = null
    pmContainer.style.display = 'none'
  },
  insertFetchedMessagesToDOM: () => {
    shoutbox.messages.forEach((shout) => {
      // If the shout is already displayed, skip it
      if (document.getElementById('shout-row-' + shout.id)) return

      shout.displayTime = new Date(shout.timestamp)
      shout.displayTime = shout.displayTime.toLocaleTimeString(shoutbox2024_conf.locale, { hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: shoutbox2024_conf.timezone })

      let shoutHTML = `
          <tr class="shout-row" id="shout-row-${shout.id}">

              <td class="shout-row-message" style="${shout.shout_to !== null ? 'background-color:' + shoutbox2024_conf.pmBGColour : ''}">
                  <a class="shout-row-user" href="#" title="Send PM to ${shout.display_name}" onClick="shoutbox.sendPMtoUser(event, ${shout.shout_from}, '${shout.display_name}')">
                      <span style="color:grey">${shout.shout_to !== null ? 'PM from' : ''} ${shout.display_name}:</span>
                  </a>
                  <span class="shoutbox_time">
                    <span title="${shout.displayTime}">
                      ${shoutbox.formatTimestamp(shout.timestamp)}
                    </span>`
      if (shoutbox2024_conf.isAdmin === '1') {
        shoutHTML += `<button type="button" class="shoutbox-admin-button" onClick="shoutbox.shoutAdmin('${shout.id}', 'delete');return false;">x</button>`
      }
      shoutHTML += `</span>
                  <span>${shoutbox.parseSmilies(shout.message)}</span>
              </td>
          </tr>
        `
      document.getElementById('shoutbox-shouts-table').insertAdjacentHTML('beforeend', shoutHTML)
      // flash the new message with a subtle background colour
      document.getElementById('shout-row-' + shout.id).style.backgroundColor = shoutbox2024_conf.newMsgBgColour
      setTimeout(() => {
        document.getElementById('shout-row-' + shout.id).style.backgroundColor = ''
      }, 1000)
    })

    // Hide rows with archived messages
    shoutbox.messages.forEach((shout) => {
      if (shout.archived === '1') {
        const shoutRow = document.getElementById('shout-row-' + shout.id)
        if (shoutRow) {
          shoutRow.style.display = 'none'
          shoutRow.innerHTML = '' // hide from public display but keep in DOM for admin options
        }
      }
    })
  },
  shoutAdmin: async (shoutID, action) => {
    if (action === 'delete') {
      const formdata = new FormData()
      formdata.append('action', 'shoutbox2024_delete_shout')
      formdata.append('shout_id', shoutID)
      formdata.append('nonce', shoutbox2024_conf.ajaxNonce)

      const requestOptions = {
        method: 'POST',
        body: formdata,
        redirect: 'follow'
      }

      await fetch(`${shoutbox.ajaxURL}`, requestOptions)
        .then((response) => response.json())
        .then((result) => {
          if (result.status === 'success') {
            document.getElementById('shout-row-' + shoutID).remove()
          }
        })
    }
  },
  clearShoutInput: () => {
    document.getElementById('shoutbox-global-shout').value = ''
  },
  submitShout: async () => {
    let message = document.getElementById('shoutbox-global-shout').value
    if (message === '') return

    // Wrap message with user preferences css
    const shoutboxPreferencesCSS = localStorage.getItem('shoutboxPreferencesCSS')
    if (shoutboxPreferencesCSS) {
      message = `<span style="${shoutboxPreferencesCSS}">${message}</span>`
    }

    // Disable this function while the message is being sent
    document.getElementById('shoutbox-submit-button').disabled = true
    document.getElementById('shoutbox-global-shout').disabled = true

    const formdata = new FormData()
    formdata.append('message', message)
    formdata.append('shout_from', shoutbox2024_conf.userID)
    formdata.append('nonce', shoutbox2024_conf.ajaxNonce)

    if (shoutbox.messagePMTo) {
      formdata.append('action', 'shoutbox2024_add_pm')
      formdata.append('shout_to', shoutbox.messagePMTo)
      formdata.append('nonce', shoutbox2024_conf.ajaxNonce)
      shoutbox.messagePMTo = null
    } else {
      formdata.append('action', 'shoutbox2024_add_shout')
      formdata.append('nonce', shoutbox2024_conf.ajaxNonce)
    }

    const requestOptions = {
      method: 'POST',
      body: formdata,
      redirect: 'follow'
    }

    await fetch(`${shoutbox.ajaxURL}`, requestOptions)
      .then((response) => response.json())
      .then((result) => {
        if (result.status === 'success') {
          shoutbox.clearShoutInput()
          document.getElementById('shoutbox-submit-button').disabled = false
          document.getElementById('shoutbox-global-shout').disabled = false
          shoutbox.refresh()
          shoutbox.focus()
        }
      })
  }
}

document.addEventListener('DOMContentLoaded', function (event) {
  // Where it all begins...
  shoutbox.init()
})
