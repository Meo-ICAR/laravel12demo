<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class InvoicesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('invoices')->delete();
        
        \DB::table('invoices')->insert(array (
            0 => 
            array (
                'id' => 1,
                'fornitore_piva' => '09006331210',
                'fornitore' => 'Hassisto Srl',
                'cliente_piva' => '10282211001',
                'cliente' => 'RACES FINANCE S.R.L.',
                'invoice_number' => 'FPR 9/25',
                'invoice_date' => '2025-04-01 00:00:00',
                'total_amount' => '732.00',
                'tax_amount' => '132.00',
                'currency' => 'EUR',
                'payment_method' => 'TP02',
                'status' => 'imported',
                'xml_data' => '<?xml version="1.0" encoding="utf-8"?>
<FatturaElettronica xmlns="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2" versione="FPR12">
<FatturaElettronicaHeader xmlns="">
<DatiTrasmissione>
<IdTrasmittente>
<IdPaese>IT</IdPaese>
<IdCodice>01879020517</IdCodice>
</IdTrasmittente>
<ProgressivoInvio>9</ProgressivoInvio>
<FormatoTrasmissione>FPR12</FormatoTrasmissione>
<CodiceDestinatario>KRRH6B9</CodiceDestinatario>
</DatiTrasmissione>
<CedentePrestatore>
<DatiAnagrafici>
<IdFiscaleIVA>
<IdPaese>IT</IdPaese>
<IdCodice>09006331210</IdCodice>
</IdFiscaleIVA>
<CodiceFiscale>09006331210</CodiceFiscale>
<Anagrafica>
<Denominazione>Hassisto Srl</Denominazione>
</Anagrafica>
<RegimeFiscale>RF01</RegimeFiscale>
</DatiAnagrafici>
<Sede>
<Indirizzo>Via Mercato </Indirizzo>
<NumeroCivico>16e</NumeroCivico>
<CAP>80018</CAP>
<Comune>Mugnano di Napoli</Comune>
<Provincia>NA</Provincia>
<Nazione>IT</Nazione>
</Sede>
<IscrizioneREA>
<Ufficio>NA</Ufficio>
<NumeroREA>NA1001542</NumeroREA>
<CapitaleSociale>10000.00</CapitaleSociale>
<SocioUnico>SM</SocioUnico>
<StatoLiquidazione>LN</StatoLiquidazione>
</IscrizioneREA>
<Contatti>
<Email>hassistosrl@gmail.com</Email>
</Contatti>
</CedentePrestatore>
<CessionarioCommittente>
<DatiAnagrafici>
<IdFiscaleIVA>
<IdPaese>IT</IdPaese>
<IdCodice>10282211001</IdCodice>
</IdFiscaleIVA>
<CodiceFiscale>10282211001</CodiceFiscale>
<Anagrafica>
<Denominazione>RACES FINANCE S.R.L.</Denominazione>
</Anagrafica>
</DatiAnagrafici>
<Sede>
<Indirizzo>VIA ALESSANDRO TORLONIA, 16/18</Indirizzo>
<CAP>00161</CAP>
<Comune>ROMA</Comune>
<Provincia>RM</Provincia>
<Nazione>IT</Nazione>
</Sede>
</CessionarioCommittente>
</FatturaElettronicaHeader>
<FatturaElettronicaBody xmlns="">
<DatiGenerali>
<DatiGeneraliDocumento>
<TipoDocumento>TD01</TipoDocumento>
<Divisa>EUR</Divisa>
<Data>2025-04-01</Data>
<Numero>FPR 9/25</Numero>
<ImportoTotaleDocumento>732.00</ImportoTotaleDocumento>
</DatiGeneraliDocumento>
</DatiGenerali>
<DatiBeniServizi>
<DettaglioLinee>
<NumeroLinea>1</NumeroLinea>
<Descrizione>DPO I trimestre 2025</Descrizione>
<Quantita>3.00</Quantita>
<PrezzoUnitario>200.00</PrezzoUnitario>
<PrezzoTotale>600.00</PrezzoTotale>
<AliquotaIVA>22.00</AliquotaIVA>
</DettaglioLinee>
<DatiRiepilogo>
<AliquotaIVA>22.00</AliquotaIVA>
<ImponibileImporto>600.00</ImponibileImporto>
<Imposta>132.00</Imposta>
</DatiRiepilogo>
</DatiBeniServizi>
<DatiPagamento>
<CondizioniPagamento>TP02</CondizioniPagamento>
<DettaglioPagamento>
<ModalitaPagamento>MP05</ModalitaPagamento>
<DataScadenzaPagamento>2025-04-01</DataScadenzaPagamento>
<ImportoPagamento>732.00</ImportoPagamento>
<IstitutoFinanziario>Credem </IstitutoFinanziario>
<IBAN>IT45Z0303203410010000104816</IBAN>
</DettaglioPagamento>
</DatiPagamento>
</FatturaElettronicaBody>
</FatturaElettronica>
',
                'created_at' => '2025-06-19 09:21:51',
                'updated_at' => '2025-06-19 09:21:51',
            ),
            1 => 
            array (
                'id' => 3,
                'fornitore_piva' => '09736331217',
                'fornitore' => 'R.C.M.S.R.L',
                'cliente_piva' => '09006331210',
                'cliente' => 'pier Giuseppe meo - Hassisto srl',
                'invoice_number' => '509',
                'invoice_date' => '2025-06-16 00:00:00',
                'total_amount' => '44.04',
                'tax_amount' => '7.94',
                'currency' => 'EUR',
                'payment_method' => '',
                'status' => 'imported',
                'xml_data' => '<?xml version="1.0"?>
<p:FatturaElettronica xmlns:p="http://ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2" versione="FPR12">
<FatturaElettronicaHeader>
<DatiTrasmissione>
<IdTrasmittente>
<IdPaese>IT</IdPaese>
<IdCodice>01558670780</IdCodice>
</IdTrasmittente>
<ProgressivoInvio>QVI2Q</ProgressivoInvio>
<FormatoTrasmissione>FPR12</FormatoTrasmissione>
<CodiceDestinatario>KRRH6B9</CodiceDestinatario>
<ContattiTrasmittente>
<Telefono>098430819</Telefono>
<Email>fatturazione-elettronica@fatturapa.com</Email>
</ContattiTrasmittente>
<PECDestinatario>hassisto@pec.it</PECDestinatario>
</DatiTrasmissione>
<CedentePrestatore>
<DatiAnagrafici>
<IdFiscaleIVA>
<IdPaese>IT</IdPaese>
<IdCodice>09736331217</IdCodice>
</IdFiscaleIVA>
<CodiceFiscale>09736331217</CodiceFiscale>
<Anagrafica>
<Denominazione>R.C.M.S.R.L</Denominazione>
</Anagrafica>
<RegimeFiscale>RF01</RegimeFiscale>
</DatiAnagrafici>
<Sede>
<Indirizzo>LOCALITA PONTE RICCIO,SNC | ZONA ASI</Indirizzo>
<CAP>80014</CAP>
<Comune>GIUGLIANO</Comune>
<Provincia>NA</Provincia>
<Nazione>IT</Nazione>
</Sede>
<IscrizioneREA>
<Ufficio>NA</Ufficio>
<NumeroREA>1053713</NumeroREA>
<CapitaleSociale>2750.00</CapitaleSociale>
<SocioUnico>SM</SocioUnico>
<StatoLiquidazione>LN</StatoLiquidazione>
</IscrizioneREA>
</CedentePrestatore>
<CessionarioCommittente>
<DatiAnagrafici>
<IdFiscaleIVA>
<IdPaese>IT</IdPaese>
<IdCodice>09006331210</IdCodice>
</IdFiscaleIVA>
<Anagrafica>
<Denominazione>pier Giuseppe meo - Hassisto srl</Denominazione>
</Anagrafica>
</DatiAnagrafici>
<Sede>
<Indirizzo>Via mercato 16</Indirizzo>
<CAP>80018</CAP>
<Comune>MUGNANO DI NAPOLI</Comune>
<Provincia>NA</Provincia>
<Nazione>IT</Nazione>
</Sede>
</CessionarioCommittente>
</FatturaElettronicaHeader>
<FatturaElettronicaBody>
<DatiGenerali>
<DatiGeneraliDocumento>
<TipoDocumento>TD01</TipoDocumento>
<Divisa>EUR</Divisa>
<Data>2025-06-16</Data>
<Numero>509</Numero>
<ImportoTotaleDocumento>44.04</ImportoTotaleDocumento>
<Arrotondamento>0.00</Arrotondamento>
</DatiGeneraliDocumento>
</DatiGenerali>
<DatiBeniServizi>
<DettaglioLinee>
<NumeroLinea>1</NumeroLinea>
<Descrizione>Toda Gattopardo - Compatibili Nespresso - Miscela Insonnia 300 Capsule</Descrizione>
<Quantita>1.00000000</Quantita>
<PrezzoUnitario>36.09836100</PrezzoUnitario>
<PrezzoTotale>36.10000000</PrezzoTotale>
<AliquotaIVA>22.00</AliquotaIVA>
</DettaglioLinee>
<DatiRiepilogo>
<AliquotaIVA>22.00</AliquotaIVA>
<ImponibileImporto>36.10</ImponibileImporto>
<Imposta>7.94</Imposta>
<EsigibilitaIVA>I</EsigibilitaIVA>
</DatiRiepilogo>
</DatiBeniServizi>
</FatturaElettronicaBody>
</p:FatturaElettronica>
',
                'created_at' => '2025-06-19 09:54:43',
                'updated_at' => '2025-06-19 09:54:43',
            ),
        ));
        
        
    }
}