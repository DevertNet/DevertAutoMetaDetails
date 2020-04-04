describe('Main Test', function() {
  it('Default', function() {
    cy.visit('/')

    cy.contains('#collapseFooterHotlineTitle', 'Service hotline')
  })
})

describe('Category: Check Guest Price', function() {
  it('Default', function() {
    cy.get('.main-navigation-link:nth-child(2)').click()
    cy.contains(':nth-child(1) > .card > .card-body > .product-info > .product-price-info > .product-price', '€19.99*')
  })
})

describe('Product: Check Guest Price', function() {
  it('Default', function() {
    cy.get(':nth-child(1) > .card > .card-body > .product-info > .product-action > .btn').click();
    cy.contains('.product-detail-price', '€19.99*')
  })
})
