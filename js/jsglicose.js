function micGlicose() {
  iniciarMicrofone("glicose", function (fala) {
    const mapa = {
      zero: 0,
      um: 1,
      dois: 2,
      três: 3,
      quatro: 4,
      cinco: 5,
      seis: 6,
      sete: 7,
      oito: 8,
      nove: 9,
      dez: 10,
      onze: 11,
      doze: 12,
      treze: 13,
      catorze: 14,
      quinze: 15,
      dezesseis: 16,
      dezessete: 17,
      dezoito: 18,
      dezenove: 19,
      vinte: 20,
      trinta: 30,
      quarenta: 40,
      cinquenta: 50,
      sessenta: 60,
      setenta: 70,
      oitenta: 80,
      noventa: 90,
      cem: 100,
      cento: 100,
      duzentos: 200,
      trezentos: 300,
      quatrocentos: 400,
      quinhentos: 500,
    };

    // Tenta extrair número direto da fala
    const match = fala.replace(/[.,]/g, "").match(/\d+/);
    let valor = match ? parseInt(match[0]) : null;

    // Tenta converter palavras em número
    if (!valor) {
      let soma = 0;
      fala
        .toLowerCase()
        .split(/\s+/)
        .forEach((p) => {
          if (mapa[p] !== undefined) soma += mapa[p];
        });
      if (soma > 0) valor = soma;
    }

    if (valor && valor >= 20 && valor <= 600) {
      document.getElementById("valor").value = valor;
      falar("Valor " + valor + " capturado. Confira e clique em salvar.");
    } else {
      falar("Não entendi. Fale o número, por exemplo: cento e vinte.");
    }
  });
}
