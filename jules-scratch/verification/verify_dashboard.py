from playwright.sync_api import sync_playwright, Page, expect
import re

def verify_dashboard_filters(page: Page):
    """
    This script verifies the refactored dashboard layout and filter functionality.
    """
    # 1. Arrange: Go to the dashboard page.
    # The development server runs on port 4200 by default.
    page.goto("http://localhost:4200/dashboard")

    # Wait for the main filter container to be visible to ensure the page has loaded.
    expect(page.get_by_role("heading", name="Filtros")).to_be_visible(timeout=10000)

    # 2. Act: Interact with the various filters.

    # Fill in the 'Nombres' text input.
    nombres_input = page.get_by_label("Nombres")
    nombres_input.fill("Usuario de Prueba")
    expect(nombres_input).to_have_value("Usuario de Prueba")

    # Fill in the 'Correo Electronico' input.
    correo_input = page.get_by_label("Correo Electronico")
    correo_input.fill("prueba@dominio.com")
    expect(correo_input).to_have_value("prueba@dominio.com")

    # Click the 'Aleman' language button.
    # We expect it to change class to show it's selected.
    aleman_button = page.get_by_role("button", name="Aleman")
    expect(aleman_button).not_to_have_class(re.compile(r"bg-primary"))
    aleman_button.click()
    expect(aleman_button).to_have_class(re.compile(r"bg-primary"))

    # Check the 'Solo contactados' checkbox.
    contactados_checkbox = page.get_by_label("Solo contactados")
    contactados_checkbox.check()
    expect(contactados_checkbox).to_be_checked()

    # 3. Screenshot: Capture the final state for visual verification.
    page.screenshot(path="/app/jules-scratch/verification/verification.png")

# --- Playwright Boilerplate ---
def run():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()
        verify_dashboard_filters(page)
        browser.close()

if __name__ == "__main__":
    run()