const fn = () => {
  const buttons = document.querySelectorAll(`.button--decorative-handler`)
  const hasButtons = buttons && buttons.length > 0

  if (hasButtons) {
    buttons.forEach((button) => {
      button.addEventListener(`click`, async (event) => {
        event.preventDefault()

        const parentRow = button.closest(`tr`)
        const isDecorative = parentRow.classList.contains(`row--decorative`)

        const id = button.getAttribute("data-id")
        if (!id) return

        const value = !isDecorative

        /*
         * Construct data to be sent to admin-ajax.php
         * Action: convert_decorative corresponds with includes/timber/construct - setup_scripts
         * Page: Used to determine position in WP_Query
         * Types: Used to determine post_types value in WP_Query
         */
        const data = {
          action: "convert_decorative",
          id: String(id),
          value: String(value),
        }

        /*
         * admin-ajax.php expects data in application/x-www-form-urlencoded format
         * To convert our data object to an acceptable format we can use
         * The URLSearchParams function: https://developer.mozilla.org/en-US/docs/Web/API/URLSearchParams
         */
        const body = new URLSearchParams(data)

        // Attempt fetch request and console error if failed
        try {
          // Use native Fetch API over libraries (jQuery/Axios)
          const response = await fetch(ajax_convert_decorative.ajaxurl, {
            body,
            method: `POST`,
          })

          // Attempt response parsing
          try {
            // Response JSON should be a HTML string
            const data = await response.json()
            const { id, message, updated } = data
            const success = message === `success`
            if (success) parentRow.classList.toggle(`row--decorative`)
          } catch (error) {
            // TODO: Add better front-end handling of errors to user
            console.error(error)
          }
        } catch (error) {
          // TODO: Add better front-end handling of errors to user
          console.error(error)
        }

        return true
      })
    })
  }
}

document.addEventListener("DOMContentLoaded", fn, false)
