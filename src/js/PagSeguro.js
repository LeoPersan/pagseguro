class PagSeguro {
    constructor (cliente) {
        this.cliente = {
            maxInstallmentNoInterest: 0,
            getSenderHash: response => console.log(response),
            getPaymentMethodsSuccess: response => console.log(response),
            getPaymentMethodsError: response => console.log(response),
            getBrandSuccess: response => console.log(response),
            getBrandError: response => console.log(response),
            getBrandComplete: response => console.log(response),
            getInstallmentsSuccess: response => console.log(response),
            getInstallmentsError: response => console.log(response),
            getInstallmentsComplete: response => console.log(response),
            ...cliente
        }
        let script = document.createElement('script')
        script.src = 'https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js'
        document.querySelector('head').appendChild(script)
        window.addEventListener('load', () => {
            PagSeguroDirectPayment.setSessionId('##SESSION##')
            PagSeguroDirectPayment.onSenderHashReady(response => {
                if(response.status == 'error')
                    return PagSeguroDirectPayment.setSessionId('##SESSION##')
                this.cliente.hash = response.senderHash
                this.cliente.getSenderHash(response)
            })
        })
    }
    setAmount(amount) {
        this.cliente.amount = amount
        PagSeguroDirectPayment.getPaymentMethods({
            amount: this.cliente.amount,
            success: response => {
                this.cliente.paymentMethods = response.paymentMethods
                this.cliente.getPaymentMethodsSuccess(response)
                this.getInstallments()
            },
            error: response => this.cliente.getPaymentMethodsError(response),
        })
    }
    setCardNumber(cardNumber) {
        this.cliente.cardNumber = cardNumber
        PagSeguroDirectPayment.getBrand({
            cardBin: this.cliente.cardNumber,
            success: response => {
                this.cliente.brand = response.brand
                this.cliente.getBrandSuccess(response)
                this.createCardToken()
                this.getInstallments()
                this.createCardToken()
            },
            error: response => this.cliente.getBrandError(response),
            complete: response => this.cliente.getBrandComplete(response),
        })
    }
    getInstallments() {
        if (
            !this.cliente.brand ||
            !this.cliente.amount ||
            !this.cliente.paymentMethods.CREDIT_CARD
        )
            return
        PagSeguroDirectPayment.getInstallments({
            amount: this.cliente.amount,
            maxInstallmentNoInterest: this.cliente.maxInstallmentNoInterest,
            brand: this.cliente.brand.name,
            success: response => {
                this.cliente.installments = response.installments[this.cliente.brand.name]
                this.cliente.getInstallmentsSuccess(response)
            },
            error: response => this.cliente.getInstallmentsError(response),
            complete: response => this.cliente.getInstallmentsComplete(response),
        })
    }
    setCvv(cvv) {
        this.cliente.cvv = cvv
        this.createCardToken()
    }
    setExpirationYear(expirationYear) {
        this.cliente.expirationYear = expirationYear
        this.createCardToken()
    }
    setExpirationMonth(expirationMonth) {
        this.cliente.expirationMonth = expirationMonth
        this.createCardToken()
    }
    createCardToken() {
        if (
            !this.cliente.cvv ||
            !this.cliente.brand ||
            !this.cliente.cardNumber ||
            !this.cliente.expirationYear ||
            !this.cliente.expirationMonth
        )
            return
        PagSeguroDirectPayment.createCardToken({
            cvv: this.cliente.cvv,
            brand: this.cliente.brand,
            cardNumber: this.cliente.cardNumber,
            expirationYear: this.cliente.expirationYear,
            expirationMonth: this.cliente.expirationMonth,
            success: response => {
                this.cliente.cardToken = reponse.token
                this.createCardTokenSuccess(response)
            },
            error: response => this.createCardTokenError(response),
            complete: response => this.createCardTokenComplete(response)
        })
    }
}
