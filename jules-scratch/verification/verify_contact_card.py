from playwright.sync_api import sync_playwright, Page, expect

def run_verification(page: Page):
    """
    Navigates to the contact card page and captures a screenshot.
    """
    # Navigate to the contact card page for a specific request ID.
    # The dev server runs on port 4200 by default.
    page.goto("http://localhost:4200/dashboard/contact/42")

    # Wait for the loading message to disappear, which indicates the API call has completed.
    # It might show an error, or the card. Either is a valid state to screenshot.
    expect(page.get_by_text("Cargando detalles...")).to_be_hidden(timeout=15000)

    # Now, find the main card container to screenshot it.
    card_container = page.locator("div.w-full.max-w-sm")

    # Wait for the card to be visible before taking a screenshot
    expect(card_container).to_be_visible()

    # Take a screenshot of the entire card.
    card_container.screenshot(path="jules-scratch/verification/verification.png")

if __name__ == "__main__":
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        try:
            run_verification(page)
            print("Verification script ran successfully.")
        except Exception as e:
            print(f"An error occurred during verification: {e}")
        finally:
            browser.close()